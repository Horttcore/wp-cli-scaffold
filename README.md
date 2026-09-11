# ralfhortt/wp-cli-scaffold

[![CI](https://github.com/Horttcore/wp-cli-scaffold/actions/workflows/ci.yml/badge.svg)](https://github.com/Horttcore/wp-cli-scaffold/actions/workflows/ci.yml)

WP-CLI scaffold commands for blocks and block patterns.

## Installation

Published package:

```bash
wp package install ralfhortt/wp-cli-scaffold
```

`ralfhortt/wp-cli-shared` is installed automatically as a Composer dependency.

Local development checkout:

```bash
wp package install /absolute/path/to/wp-cli-scaffold
```

Update after local edits:

```bash
wp package remove ralfhortt/wp-cli-scaffold
wp package install /absolute/path/to/wp-cli-scaffold
```

## Commands

| Command | Description |
|---|---|
| `wp scaffold create-block [slug]` | Scaffold a block via `@wordpress/create-block@4` (theme or plugin) |
| `wp scaffold pattern` | Create `{active-theme}/patterns/{slug}.php` |
| `wp scaffold starter-content` | Create a starter content pattern |
| `wp scaffold query-loop` | Create a query loop pattern |
| `wp scaffold template [slug]` | Create `{active-theme}/templates/{slug}.html` |
| `wp scaffold template-part [slug]` | Create `{active-theme}/parts/{slug}.html` |
| `wp scaffold style-variation [slug]` | Create `{active-theme}/styles/{slug}.json` |
| `wp scaffold theme-json-section` | Merge a payload into a `theme.json` section |

All commands require `--path=/path/to/wordpress`.

## Examples

```bash
wp scaffold create-block todo-list --destination=theme --title="Todo List" --path=/path/to/wordpress
wp scaffold pattern --title="Hero Banner" --categories=banner --path=/path/to/wordpress
wp scaffold query-loop --title="Latest Posts" --post-type=post --path=/path/to/wordpress
wp scaffold template single --path=/path/to/wordpress
wp scaffold template-part header --area=header --path=/path/to/wordpress
wp scaffold style-variation ocean --title="Ocean" --primary="#0a5cff" --background="#f4f8ff" --text="#0f172a" --path=/path/to/wordpress
wp scaffold theme-json-section --section=settings.color.palette --json='[{"slug":"brand","name":"Brand","color":"#0a5cff"}]' --path=/path/to/wordpress
```

## Flags

- `--theme=<slug>` — target a specific theme
- `--destination=theme|plugin` — for create-block
- `--plugin=<slug>` — add block to existing plugin
- `--area=header|footer|sidebar|uncategorized` — for template-part
- `--dry-run` — print `theme.json` merge result without writing
- `--force` — overwrite existing files

## Development

```bash
composer stan
composer lint
composer test
```
