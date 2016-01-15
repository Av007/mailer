'use strict';

var gulp = require('gulp');
var sass = require('gulp-sass');
var minifyCss = require('gulp-minify-css');

// File paths to various assets are defined here.
var PATHS = {
    js: [
        'bower_components/jquery/dist/jquery.min.js',
        'bower_components/jquery/dist/jquery.min.map'
    ]
};

gulp.task('js', function () {
    return gulp.src(PATHS.js)
        .pipe(gulp.dest('js'));
});

gulp.task('css', function () {
    return gulp.src(['stylesheets/*.css'])
        .pipe(gulp.dest('css'));
});

// Compile Sass into CSS
gulp.task('sass', function () {
    gulp.src('./scss/*.scss')
        .pipe(sass.sync().on('error', sass.logError))
        .pipe(minifyCss())
        .pipe(gulp.dest('./css'));
});

gulp.task('sass:watch', function () {
    gulp.watch('./sass/*.scss', ['sass']);
});

gulp.task('default', ['sass', 'js']);

gulp.task('sass:watch', function () {
    gulp.watch('./sass/*.scss', ['sass']);
});
