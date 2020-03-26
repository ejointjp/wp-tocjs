/////////////////////////////////////////////////////////////////////////////////////

// requirement

/////////////////////////////////////////////////////////////////////////////////////

var gulp = require('gulp')
var del = require('del')
var path = require('path')
var argv = require('minimist')(process.argv.slice(2))
var runSequence = require('run-sequence')

/////////////////////////////////////////////////////////////////////////////////////

// config

/////////////////////////////////////////////////////////////////////////////////////

var config = require('./_config')
var dir = config.dir
var file = config.file
const ProjectRoot = dir.root

/////////////////////////////////////////////////////////////////////////////////////

// tasks

/////////////////////////////////////////////////////////////////////////////////////

// clean assets file
gulp.task('clean', function () {
  var assets = path.join(dir.rel.dist, dir.assets, dir.all, file.all)
  var caches = path.join(dir.rel.dist, dir.cache, dir.all, file.all)
  var deleteFiles = [assets]

  if(argv.cache) {
    deleteFiles = [caches]
  }

  if(argv.all) {
    deleteFiles = [assets, caches]
  }

  return del(deleteFiles).then(function (paths) {
    console.log('Deleted files: \n', paths.join('\n'))
  })
})

gulp.task('copy', function () {
  // var src = path.join(dir.rel.src, dir.sass, 'wp-tocjs.scss')
  // const csssrc = path.join(ProjectRoot, 'node_modules', 'tocjs', 'dist', '**', '*.css')
  const jsSrc = path.join(ProjectRoot, 'node_modules', 'tocjs', 'dist', dir.all, '*.js')
  // const cssdist = path.join(dir.rel.dist, dir.assets, dir.css)
  const jsDist = path.join(dir.rel.dist, dir.assets)
  const sassSrc = path.join(ProjectRoot, dir.src, dir.sass, dir.all, '*.scss')
  const sassDist = path.join(dir.rel.dist, dir.assets, dir.sass)
  // gulp.src(csssrc)
  //   .pipe(gulp.dest(cssdist))

  gulp.src(jsSrc)
    .pipe(gulp.dest(jsDist))

  gulp.src(sassSrc)
    .pipe(gulp.dest(sassDist))
})

gulp.task('build', function (callback) {
  argv.cache = true

  runSequence(
    // 'clean',
    'copy',
    callback
  )
})
