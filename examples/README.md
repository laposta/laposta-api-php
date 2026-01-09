# Laposta API PHP – Examples

This directory contains example scripts demonstrating how to use the Laposta API PHP library.

## Structure

- `bootstrap.php`: Sets up the environment for running example scripts. It loads dependencies using the project's
  standalone `autoload.php` (for local examples only; WordPress plugins should use the scoped release build instead)
  and loads settings from `config.php`. This setup is intended only for the examples and not for use in production
  code.
- `config-example.php`: A template file to create your own `config.php` for use in examples.
- Subdirectories (e.g. `list/`, `campaign/`, `member/`) group example scripts by API resource.
- Each PHP file within a subdirectory demonstrates a specific API call (e.g. `list/all.php`, `list/get.php`).
- The `misc/` subdirectory contains general-purpose examples that demonstrate broader integration patterns,
    such as a retry mechanism, middleware injection, or a standalone HTML form. These are not tied to a specific
    API resource, but useful when extending or adapting the Laposta client for your own stack.

## Setup

In this `examples` directory, copy `config-example.php` to `config.php` and fill in your API credentials and IDs:

```php
// examples/config.php
return [
   'LP_EX_API_KEY' => 'your_api_key',
   'LP_EX_LIST_ID' => 'your_list_id',
   // Add other values as needed
];
```

## Running the Examples

From the project root, you can run the examples using the PHP CLI like this:

```bash
php examples/list/all.php
php examples/list/get.php
```

---

⬅️ Back to the [main README](../README.md)
