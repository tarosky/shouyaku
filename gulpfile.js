const gulp        = require( 'gulp' ),
      fs          = require( 'fs' ),
      $           = require( 'gulp-load-plugins' )(),
      eventStream = require( 'event-stream' ),
      webpack       = require( 'webpack-stream' ),
      webpackBundle = require( 'webpack' ),
      named         = require( 'vinyl-named' );

// Sass
gulp.task( 'sass', function() {
  return gulp.src([
    './src/scss/**/*.scss'
  ])
    .pipe( $.plumber({
      errorHandler: $.notify.onError( '<%= error.message %>' )
    }) )
    .pipe( $.sourcemaps.init({loadMaps: true}) )
    .pipe( $.sassBulkImport() )
    .pipe( $.sass({
      errLogToConsole: true,
      outputStyle: 'compressed',
      includePaths: [
        './src/scss',
        './node_modules/bootstrap/scss'
      ]
    }) )
    .pipe( $.autoprefixer({browsers: [ 'last 2 version', '> 5%' ]}) )
    .pipe( $.sourcemaps.write( './map' ) )
    .pipe( gulp.dest( './assets/css' ) );
});


// Minify All
gulp.task( 'js', function() {
  return gulp.src([ './src/js/**/*.js' ])
    .pipe( $.plumber({
      errorHandler: $.notify.onError( '<%= error.message %>' )
    }) )
    .pipe( named( function( file ) {
      return file.relative.replace( /\.[^.]+$/, '' );
    }) )
    .pipe( webpack({
      mode: 'production',
      devtool: 'source-map',
      module: {
        rules: [
          {
            test: /\.js$/,
            exclude: /(node_modules|bower_components)/,
            use: {
              loader: 'babel-loader',
              options: {
                presets: [ '@babel/preset-env' ]
              }
            }
          }
        ]
      }
    }, webpackBundle ) )
    .pipe( gulp.dest( './assets/js/' ) );
});


// JS Hint
gulp.task( 'eslint', function() {
  return gulp.src([
    './src/js/**/*.js'
  ])
    .pipe( $.eslint({ useEslintrc: true }) )
    .pipe( $.eslint.format() );
});

// Copy library
gulp.task( 'copylib', function() {
  // return eventStream.merge();
});

// Image min
gulp.task( 'imagemin', function() {
  return gulp.src( './src/img/**/*' )
    .pipe( $.imagemin([
      $.imagemin.gifsicle({interlaced: true}),
      $.imagemin.jpegtran({progressive: true}),
      $.imagemin.optipng({optimizationLevel: 5}),
      $.imagemin.svgo({
        plugins: [
          {removeViewBox: true},
          {cleanupIDs: false}
        ]
      })
    ], {verbose: true}) )
    .pipe( gulp.dest( './assets/img' ) );
});

// watch
gulp.task( 'watch', function() {
  // Make SASS
  gulp.watch( 'src/scss/**/*.scss', [ 'sass' ]);
  // JS
  gulp.watch([ 'src/js/**/*.js' ], [ 'js', 'eslint' ]);
  // Minify Image
  gulp.watch( 'src/img/**/*', [ 'imagemin' ]);
});

// Build
gulp.task( 'build', [ 'copylib', 'eslint', 'js', 'sass', 'imagemin' ]);

// Default Tasks
gulp.task( 'default', [ 'watch' ]);
