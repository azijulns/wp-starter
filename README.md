# BlackDevs WP Starter

A boilerplate WordPress plugin designed to help developers kickstart new projects quickly.

## Features

- Standardized plugin structure
- Composer support for dependency management
- Easy integration via `composer create-project`
- Built-in `bdevs` CLI for scaffolding classes
- Ready-to-extend codebase for custom functionality

## Installation

```bash
composer create-project blackdevs/wp-starter my-plugin
```

## CLI — bdevs

The `bdevs` CLI tool lets you scaffold new classes and automatically wire them into the plugin.

### Setup (one-time)

Add the following function to your PowerShell profile (`$PROFILE`):

```powershell
function bdevs { php "D:\plugins\wp-starter\bin\bdevs" @args }
```

### Usage

```bash
bdevs <command> [arguments]
```

### Commands

| Command | Description |
|---|---|
| `bdevs class <ClassName> [folder]` | Create a class file and auto-wire it into the plugin |
| `bdevs help` | Show help |

### Examples

```bash
# Creates classes/my-feature.php (default folder: classes/)
bdevs class MyFeature

# Creates includes/post-types.php
bdevs class PostTypes includes

# Creates widgets/menu-widget.php
bdevs class Menu_Widget widgets
```

When you run `bdevs class`, it will automatically:

1. Create the class file with the correct namespace
2. Add `require_once` inside `includes()` in the main plugin file
3. Add `new ClassName()` inside `init_plugin()` in the main plugin file

## Project Structure

```
bin/
  bdevs          # CLI dispatcher
  setup.php      # Interactive setup wizard
classes/         # Your custom classes (default bdevs target)
includes/        # Core plugin includes
widgets/         # Widget classes
assets/
  css/
  js/
  img/
```