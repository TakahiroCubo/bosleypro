
/*
 |--------------------------------------------------------------------------
 | Browser-sync config file
 |--------------------------------------------------------------------------
 |
 | For up-to-date information about the options:
 |   http://www.browsersync.io/docs/options/
 |
 | There are more options than you see here, these are just the ones that are
 | set internally. See the website for more info.
 |
 |
 */
module.exports = {
    "files": ["./htdocs/*.html","./htdocs/**/*.html","./htdocs/**/*.css","./htdocs/**/*.js"],
    server: {
        baseDir: "htdocs",
        directory: true
    },
    "startPath":"",
    "proxy": false,
    "port": 3000,
    "xip": false,
    "notify": true,
    "minify": true,
};