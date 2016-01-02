var gulp = require('gulp');

gulp.task('css', function () {
    return gulp.src(['stylesheets/*.css'])
        .pipe(gulp.dest('css'));
});

gulp.task('default', []);