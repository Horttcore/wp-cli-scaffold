# ralfhortt/wp-cli-scaffold

WP-CLI scaffold commands for blocks and block patterns.

## Installation

Install the shared library first, then this package:

```bash
wp package install /path/to/packages/wp-cli-shared
wp package install /path/to/packages/wp-cli-scaffold
```

## Commands

| Command | Description |
|---|---|
| `wp scaffold create-block [slug]` | Scaffold a block via `@wordpress/create-block@4` (theme or plugin) |
| `wp scaffold pattern` | Create `{active-theme}/patterns/{slug}.php` |
| `wp scaffold starter-content` | Create a starter content pattern |
| `wp scaffold query-loop` | Create a query loop pattern |

All commands require `--path=/path/to/wordpress`.

## Examples

```bash
wp scaffold create-block todo-list --destination=theme --title="Todo List" --path=/path/to/wordpress
wp scaffold pattern --title="Hero Banner" --categories=banner --path=/path/to/wordpress
wp scaffold query-loop --title="Latest Posts" --post-type=post --path=/path/to/wordpress
```

## Flags

- `--theme=<slug>` — target a specific theme
- `--destination=theme|plugin` — for create-block
- `--plugin=<slug>` — add block to existing plugin
- `--force` — overwrite existing files
