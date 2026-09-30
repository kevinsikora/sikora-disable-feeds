#!/usr/bin/env bash
# Verify Sikora Disable Feeds on a live WordPress site.
# Usage: ./test-feeds.sh https://example.com
set -euo pipefail

if [[ $# -lt 1 ]]; then
  echo "Usage: $0 <site-url>" >&2
  echo "Example: $0 https://example.com" >&2
  exit 2
fi

SITE_URL="${1%/}"
FAILURES=0

red() { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
info() { printf '%s\n' "$*"; }

pass() {
  green "PASS: $*"
}

fail() {
  red "FAIL: $*"
  FAILURES=$((FAILURES + 1))
}

info "Testing ${SITE_URL}"
info "Feed URLs should end in HTTP 404 (host redirects such as apex→www are allowed)."
info

# --- 1. Homepage should not advertise WordPress feed discovery links. ---
info "Checking homepage source for feed discovery links..."
if ! HOME_HTML="$(curl -fsSL --max-time 30 "${SITE_URL}/")"; then
  fail "Could not fetch homepage ${SITE_URL}/"
  HOME_HTML=""
fi

if [[ -n "$HOME_HTML" ]]; then
  FEED_LINKS="$(
    printf '%s\n' "$HOME_HTML" \
      | grep -Ei '<link[^>]+application/(rss|atom)\+xml' \
      || true
  )"

  if [[ -z "$FEED_LINKS" ]]; then
    pass "No application/rss+xml or application/atom+xml links in homepage source"
  else
    fail "Found feed discovery link(s) in homepage source"
    printf '%s\n' "$FEED_LINKS" | sed 's/^/  /'
  fi

  if printf '%s\n' "$HOME_HTML" | grep -Ei '<link[^>]+EditURI[^>]+rsd\.xml' >/dev/null; then
    fail "RSD discovery link still present in homepage source"
  else
    pass "No RSD discovery link in homepage source"
  fi
fi

info

# --- 2. Feed URLs should resolve to HTTP 404 without feed XML. ---
FEED_PATHS=(
  /feed/
  /feed/rss/
  /feed/rss2/
  /feed/atom/
  /feed/rdf/
  /comments/feed/
)

info "Checking feed URL responses..."
for path in "${FEED_PATHS[@]}"; do
  url="${SITE_URL}${path}"
  body_file="$(mktemp)"

  # Follow host redirects; capture final status and body.
  meta="$(
    curl -sL --max-redirs 8 --max-time 45 \
      -o "${body_file}" \
      -w '%{http_code}\t%{url_effective}\t%{num_redirects}' \
      "$url" || true
  )"

  status="$(printf '%s' "$meta" | cut -f1)"
  final_url="$(printf '%s' "$meta" | cut -f2)"

  if [[ -z "$status" || "$status" == "000" ]]; then
    fail "${path} — no response"
    rm -f "${body_file}"
    continue
  fi

  if [[ "$status" != "404" ]]; then
    fail "${path} — expected final HTTP 404, got ${status} at ${final_url}"
    rm -f "${body_file}"
    continue
  fi

  if head -c 2048 "${body_file}" | grep -Ei '<rss[[:space:]>]|<feed[[:space:]>]' >/dev/null; then
    fail "${path} — final response still looks like a feed"
    rm -f "${body_file}"
    continue
  fi

  pass "${path} → HTTP 404 (${final_url})"
  rm -f "${body_file}"
done

info
if [[ "$FAILURES" -eq 0 ]]; then
  green "All checks passed."
  exit 0
fi

red "${FAILURES} check(s) failed."
exit 1
