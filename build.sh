#!/usr/bin/env bash
# Build an installable WordPress plugin zip for Sikora Disable Feeds.
set -euo pipefail

PLUGIN_SLUG="sikora-disable-feeds"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PLUGIN_FILE="${SCRIPT_DIR}/${PLUGIN_SLUG}.php"
BUILD_DIR="${SCRIPT_DIR}/build"
ZIP_NAME="${PLUGIN_SLUG}.zip"
BUILD_ZIP="${BUILD_DIR}/${ZIP_NAME}"
ROOT_ZIP="${SCRIPT_DIR}/${ZIP_NAME}"

cleanup() {
  rm -rf "${BUILD_DIR}"
}
trap cleanup EXIT

if [[ ! -f "${PLUGIN_FILE}" ]]; then
  echo "error: missing ${PLUGIN_FILE}" >&2
  exit 1
fi

# Read version from the plugin header (for the build message only).
VERSION="$(
  grep -E '^[[:space:]]*\*?[[:space:]]*Version:[[:space:]]*' "${PLUGIN_FILE}" \
    | head -n1 \
    | sed -E 's/^[[:space:]]*\*?[[:space:]]*Version:[[:space:]]*//' \
    | tr -d '[:space:]'
)"

if [[ -z "${VERSION}" ]]; then
  echo "error: could not read Version from ${PLUGIN_FILE}" >&2
  exit 1
fi

echo "Building ${PLUGIN_SLUG} v${VERSION}..."

rm -rf "${BUILD_DIR}"
mkdir -p "${BUILD_DIR}/${PLUGIN_SLUG}"

# Only ship files WordPress needs to install and run the plugin.
# README.md stays in the repo for GitHub; it is not packaged.
cp "${PLUGIN_FILE}" "${BUILD_DIR}/${PLUGIN_SLUG}/"
if [[ -f "${SCRIPT_DIR}/readme.txt" ]]; then
  cp "${SCRIPT_DIR}/readme.txt" "${BUILD_DIR}/${PLUGIN_SLUG}/"
fi

# Create the zip inside build/, with the plugin folder as the zip root.
(
  cd "${BUILD_DIR}"
  zip -r -q "${ZIP_NAME}" "${PLUGIN_SLUG}" \
    -x '*/.DS_Store' '*/__MACOSX/*' '*/.*'
)

# Copy the installable zip to the project root, then build/ is deleted by cleanup.
rm -f "${ROOT_ZIP}"
cp "${BUILD_ZIP}" "${ROOT_ZIP}"

echo "Created:"
echo "  ${ROOT_ZIP}"
echo
echo "Upload the zip via WordPress → Plugins → Add New → Upload Plugin."
unzip -l "${ROOT_ZIP}"
