# BlackDevs WP Starter

A boilerplate WordPress plugin designed to help developers kickstart new projects quickly.

## Features

- Standardized plugin structure
- Composer support for dependency management
- Easy integration via `composer create-project`
- Built-in `bdevs` CLI for scaffolding classes, widgets, and assets
- Ready-to-extend codebase for custom functionality

## Installation

```bash
composer create-project blackdevs/wp-starter my-plugin
```

## CLI — bdevs

The `bdevs` CLI tool scaffolds classes, Elementor widgets, and asset files — and automatically wires everything into the plugin.

### Setup (one-time)

Add the following function to your PowerShell profile (`$PROFILE`):

```powershell
function bdevs { php "D:\plugins\wp-starter\bin\bdevs" @args }
```

### Usage

```bash
bdevs <command> [arguments]
```

---

### `bdevs class`

Creates a PHP class file and auto-wires it into the plugin.

```bash
bdevs class <ClassName> [folder]
```

| Folder | Result |
|---|---|
| _(default)_ `classes` | Plain class → wired into `includes()` + `init_plugin()` |
| `includes` | Plain class → wired into `includes()` + `init_plugin()` |
| `widgets` | **Elementor widget stub** → wired into `widgets/init.php` |

```bash
# Creates classes/my-feature.php
bdevs class MyFeature

# Creates includes/post-types.php
bdevs class PostTypes includes

# Creates widgets/my-slider.php (Elementor widget)
bdevs class My_Slider widgets
```

When folder is **not** `widgets`, automatically:
1. Creates the class with the correct namespace
2. Adds `require_once` inside `includes()` in the main plugin file
3. Adds `new ClassName()` inside `init_plugin()`

When folder is **`widgets`**, automatically:
1. Creates an Elementor `Widget_Base` stub with `get_name()`, `get_title()`, `register_controls()`, `render()`
2. Adds `require_once` inside `init_widgets()` in `widgets/init.php`
3. Registers the widget via `$widgets_manager->register()`

---

### `bdevs css` / `bdevs js`

Creates an asset file and enqueues it in `AssetsManager`.

```bash
bdevs css <filename> [admin]
bdevs js  <filename> [admin]
```

| Flag | Hook |
|---|---|
| _(none)_ | `wp_enqueue_scripts` → `enqueue_scripts()` |
| `admin` | `admin_enqueue_scripts` → `enqueue_admin_scripts()` |

```bash
# Frontend CSS/JS
bdevs css my-feature
bdevs js  my-feature

# Admin CSS/JS
bdevs css admin-styles admin
bdevs js  admin-panel  admin
```

---

### `bdevs help`

```bash
bdevs help
```

---

## Project Structure

```
bin/
  bdevs          # CLI dispatcher
  setup.php      # Interactive setup wizard
classes/         # Your custom classes (default bdevs target)
includes/        # Core plugin includes
  assets-manager.php  # Enqueue frontend & admin scripts/styles
widgets/         # Elementor widget classes
  init.php       # Widget registration
assets/
  css/
  js/
  img/
```