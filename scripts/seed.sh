#!/usr/bin/env bash
set -euo pipefail

npx wp-env run cli wp eval-file wp-content/themes/mitka/scripts/seed.php
