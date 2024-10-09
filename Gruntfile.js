/* eslint-disable sort-keys */
module.exports = function (grunt) {
  require('load-grunt-tasks')(grunt); // This already loads npm tasks so individual load might not be necessary
  const pkg = grunt.file.readJSON('package.json');
  grunt.initConfig({
    pkg,

    // Configuration for PHP CodeSniffer
    phpcs: {
      application: {
        src: ['src/**/*.php'],
      },
      options: {
        bin: 'vendor/bin/phpcs',
        standard: './.phpcs.xml.dist',
      },
    },

    // Webpack configuration
    webpack: {
      options: require('./webpack.config.js'),
      build: {},
    },

    composer: {
      options: {
        usePhp: false,
        flags: ['no-dev'],
        preferDist: true,
        cwd: 'build/' + pkg.name + '/',
      },
    },

    // Copy task configuration
    copy: {
      build: {
        files: [
          {
            expand: true,
            src: [
              'composer.json',
              'src/**',
              'vendor_prefixed/**/*.php',
              'assets/**',
              '*.php',
              '!src/**/*.ts',
              '!src/**/*.tsx',
            ],
            dest: 'build/' + pkg.name + '/',
          },
        ],
      },
    },

    // Compress (zip) task configuration
    compress: {
      build: {
        options: {
          archive: 'build/' + pkg.name + '.zip',
          mode: 'zip',
        },
        files: [
          {
            expand: true,
            cwd: 'build/' + pkg.name + '/',
            src: ['**/*'],
            dest: '/',
          },
        ],
      },
    },
  });

  // Load the plugins
  grunt.loadNpmTasks('grunt-phpcs');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-compress'); // Load the compress task

  // Default task
  grunt.registerTask('default', ['phpcs', 'webpack', 'copy', 'composer:install', 'compress']);
};
