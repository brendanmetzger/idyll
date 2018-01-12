This is my rewrite, where you see EXT's, I put in the files I might expect to generate with the framework, which would be: `html|xml|css|js|svg|json|jpe?g|png`
```

RewriteEngine On
RewriteOptions Inherit
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([A-z]*)\/?([A-z]*)\/?((?:[A-z0-9-:\/_*=]|\.[^A-z])*)(?:\.(EXT's))?$ index.php?_r_[]=$1&_r_[]=$2&_p_=$3&_e_=$4 [B,QSA,L]

```