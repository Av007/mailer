'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var minifyCss = require('gulp-minify-css');

// File paths to various assets are defined here.
var PATHS = {
    js: [
        'node_modules/jquery/dist/jquery.min.js',
        'node_modules/jquery/dist/jquery.min.map'
    ]
};

gulp.task('js', function () {
    return gulp.src(PATHS.js)
        .pipe(gulp.dest('web/js'));
});

gulp.task('css', function () {
    return gulp.src(['web/stylesheets/*.css'])
        .pipe(gulp.dest('web/css'));
});

// Compile Sass into CSS
gulp.task('sass', function () {
    gulp.src('web/scss/main.scss')
        .pipe(sass({
            includePaths: 'node_modules'
         }).on('error', sass.logError))
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(minifyCss())
        .pipe(gulp.dest('web/css'));
});

gulp.task('sass:watch', function () {
    gulp.watch('web/sass/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'js']);

gulp.task('sass:watch', function () {
    gulp.watch('web/sass/*.scss', ['sass']);
});
