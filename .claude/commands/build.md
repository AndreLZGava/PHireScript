Run the PHireScript compiler in BUILD mode, compiling `.phs` files to `.php` using paths from `PHireScript.json`.

From your root sandbox project

```bash
cd /phirescript && php bin/build
```

If there are compilation errors, show the error message and the relevant `.phs` file content around the failing line.
