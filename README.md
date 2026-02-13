# Wayback Machine Link Fixer (WordPress Plugin)

This project provides a WordPress plugin that scans rendered HTML for broken external links and replaces them with the closest Internet Archive Wayback Machine snapshot URL.

## Plugin path

- `wayback-link-fixer/wayback-link-fixer.php`

## Development

```bash
composer install
composer run lint
composer run test
```

## QA

Project QA also passes with:

```bash
python3 /Users/victorcamilojimenezvargas/agent-hq/jobs/run_project_qa.py /Users/victorcamilojimenezvargas/Projects/wp-wayback-machine-link-fixer
```
