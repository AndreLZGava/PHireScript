Run all quality checks: Rector (refactor), php-cs-fixer (format), PHPStan level 9 + PHPMD (analyse).

From your root sandbox project

```bash
cd /phirescript && composer quality
```

If any check fails, report exactly which rule was violated and in which file. Do not suppress warnings — fix them.
