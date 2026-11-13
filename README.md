# HAC research publishing — WordPress concept demo

A concept prepared by Ben Upham for the Housing Assistance Council's ruralhome.org website redesign RFP (November 2026). Not an official HAC site or plugin.

**Open the demo:** https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/benupham/hac-research-demo/main/blueprint.json

It runs WordPress entirely in your browser (WordPress Playground). Nothing you change is saved anywhere.

## What's in it

- **Sample brief:** HAC's June 2026 Rural Research Brief, *USDA's Section 515 Multifamily Housing Portfolio Continues to Shrink*, as a WordPress post instead of a PDF and an Infogram embed. Text is HAC's; chart figures are HAC's own data.
- **Data chart block** (`hac/chart`): paste a table from a spreadsheet, pick columns, bars or stacked, and the block draws the chart. A description is required before publishing, and every chart outputs a data table, so charts are accessible and searchable by default. Extra columns become a toggle readers can switch between.
- **Patterns** in the "HAC research" category: Research brief (full template), Key findings, Headline numbers.

## Try it

1. Open the link above. You land on the brief.
2. Use "Open this brief in the editor" in the top bar.
3. Click a chart and edit its data, or add a new one: type `/chart` in an empty paragraph.
4. Start a new post and insert the "Research brief (full template)" pattern.

## Files

- `blueprint.json` — the Playground blueprint (self-contained: it writes the plugin files and creates the sample post).
- `hac-research-blocks/` — the plugin source. No build step.
