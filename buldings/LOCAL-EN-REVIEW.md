# Local English Render Review

Run this immediately after Prompt 2 creates `content-en.json`. English is approved from the rendered Local page, not from JSON alone.

## Inputs

- Absolute path to `content-en.json`
- Exact Local building page path, for example `apartment-buildings/the-monarchy`

## Load the draft

From the project root, validate first:

```bash
"/Users/vincent/Library/Application Support/Local/lightning-services/php-8.4.4+2/bin/darwin-arm64/bin/php" \
  -c "/Users/vincent/Library/Application Support/Local/run/I7Rx2v6bX/conf/php/php.ini" \
  "/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar" \
  --path="/Users/vincent/Local Sites/hrd/app/public" \
  eval-file scripts/load-building-content-local.php \
  "apartment-buildings/[PAGE SLUG]" \
  "/absolute/path/to/content-en.json" \
  dry-run
```

Remove only the final `dry-run` argument after validation succeeds. The loader refuses non-`hrd.local` sites, requires the shared building template, validates exactly eight string fields and keeps the previous English meta value in `hrd_building_content_en_local_backup` when it changes.

## Review the rendered page

Open `http://hrd.local/apartment-buildings/[PAGE SLUG]/` and review desktop and mobile.

Check the page in normal reading order:

- hero states the building and renter fit clearly;
- quick facts and body copy do not contradict each other;
- paragraphs and information groups are easy to scan;
- dated rental references are visibly distinguished from current facts;
- FAQ questions and answers render as separate items;
- related links are distinct and usable;
- long words, addresses and links do not overflow on mobile;
- empty gallery copy remains appropriate until images are supplied;
- listing cards are treated as separate inventory and do not change the editorial claims;
- no source IDs, research labels, URLs or internal warnings appear in public copy.

If the rendered page fails, revise `content-en.json`, load it again and repeat the review. Mark English approvals only after the Local page passes.
