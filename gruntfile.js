module.exports = function(grunt) {
  'use strict';

  //INIT
  require('jit-grunt')(grunt);
  grunt.initConfig({});

  grunt.loadNpmTasks('grunt-force-task');

  grunt.config('pkg', grunt.file.readJSON('package.json'));

  grunt.config('env', grunt.option('env') || process.env.GRUNT_ENV || 'dev');
  grunt.log.writeln('Generating build for: ' + grunt.config('env'));

  grunt.config('compress', grunt.option('compress') || process.env.GRUNT_COMPRESS || grunt.config('env') === 'live');
  grunt.log.writeln('Compression is: ' + grunt.config('compress'));

  grunt.config('build', Math.random().toString(36).substr(2, 5));
  grunt.log.writeln('Build: ' + grunt.config('build'));

  //CONFIG
  grunt.config('config', {
    source: 'source/',
    build: 'build/',
    ciroot: 'ci',
    phpunitroot: 'phpunit'
  });

  grunt.config('vendor_css', [
    'node_modules/normalize.css/normalize.css',
    'node_modules/bootstrap/dist/css/bootstrap.min.css',
    'node_modules/font-awesome/css/font-awesome.min.css',
  ]);

  grunt.config('vendor_js', [
    'temp/modernizr.js',
    'node_modules/jquery/dist/jquery.js',
    'node_modules/bootstrap/dist/js/bootstrap.min.js'
  ]);

  //TASK LIST

  var installList = [
    'clean:node_ci',
    'clean:ifx',
    'clean:phpunit',
    'shell:codeignighter',
    'shell:ifx',
    'shell:phpunit',
    'shell:phpunit_install'
  ];

  var retestList = [
    'copy:sourcephp',
    'copy:phpunitci',
    'copy:citests',
    'shell:tests'
  ];

  var testList = [
    'force:clean:default',
    'copy:codeignighter',
    'copy:sourcephp',
    'copy:ciconfig',
    'copy:phpunitci',
    'copy:citests',
    'shell:tests'
  ];

  var testDebugList = [
    'copy:sourcephp',
    'copy:phpunitci',
    'copy:citests',
    'shell:tests_debug'
  ];

  var serverList = [
    'php:server',
    'php:worker',
    'browserSync',
    'watch',
  ];

  var taskList = [
    'force:clean:default',
    'copy:codeignighter',
    'copy:ifx',
    'copy:assets',
    'concat:sourceless',
    'lesslint',
    'less',
    'jshint:all',
    'modernizr',
    'concat:vendorjs',
    'concat:sourcejs',
    'concat:appjs',
    'concat:appcss',
    'copy:fonts',
    'copy:images',
    'copy:sourcephp',
    'copy:htaccess'
  ];

  var devList = taskList.slice();

  for (var i in serverList) {
    devList.push(serverList[i]);
  }

  grunt.registerTask('install', installList);
  grunt.registerTask('test', testList);
  grunt.registerTask('retest', retestList);
  grunt.registerTask('debug', testDebugList);
  grunt.registerTask('server', serverList);
  grunt.registerTask('run', devList);
  grunt.registerTask('default', devList);
  grunt.registerTask('ifx-renew', ['clean:ifx', 'shell:ifx', 'copy:ifx']);

  grunt.config('onLESSChange', [
    'concat:sourceless',
    'less:run',
    'concat:sourceless',
    'concat:appcss',
    'copy:htaccess'
  ]);

  grunt.config('onJSChange', [
    'jshint:all',
    'concat:vendorjs',
    'concat:sourcejs',
    'concat:appjs',
    'copy:htaccess'
  ]);

  grunt.config('onPHPChange', [
    'copy:sourcephp',
    'copy:htaccess'
  ]);


  grunt.config('onAssetChange', [
    'copy:assets',
    'copy:htaccess'
  ]);

  //TASK CONFIG

  grunt.config('browserSync', {
    build: {
      src: '<%=config.build %>**/*.*'
    },
    options: {
      proxy: '<%= php.server.options.hostname %>:<%= php.server.options.port %>',
      watchTask: true,
      logLevel: 'warn',
      reloadDelay: 3000,
      injectChanges: false
    }
  });

  grunt.config('clean', {
    application: ['temp', '<%= config.build %>application'],
    default: ['<%=config.build %>'],
    ifx: ['ifx'],
    node_ci: ['<%=config.ciroot %>'],
    phpunit: ['<%=config.phpunitroot %>']
  });

  grunt.config('concat', {
    vendorjs: {
      nonull: true,
      src: grunt.config('vendor_js'),
      dest: 'temp/js/vendor.js'
    },

    sourceless: {
      src: [
        '<%=config.source %>less/*.less',
        '<%=config.source %>less/**/*.less',
      ],
      dest: 'temp/compiled.less'
    },

    sourcejs: {
      src: [
        '<%=config.source %>js/*.js',
        '<%=config.source %>js/**/*.js'
      ],
      dest: 'temp/js/source.js'
    },

    appjs: {
      nonull: true,
      src: [
        'temp/js/vendor.js',
        'temp/js/source.js'
      ],
      dest: '<%=config.build %><%=config.assets %>app.js'
    },

    appcss: {
      nonull: true,
      src: grunt.config('vendor_css').concat([
        'temp/css/less.css'
      ]),
      dest: '<%=config.build %><%=config.assets %>app.css'
    }
  });

  grunt.config('copy', {

    fonts: {
      cwd: 'node_modules/font-awesome/fonts/',
      src: '**',
      dest: '<%=config.build %>fonts/',
      expand: true
    },

    htaccess: {
      cwd: '<%=config.source %>',
      src: '.htaccess',
      dest: '<%=config.build %>',
      expand: true
    },

    images: {
      expand: true,
      cwd: '<%=config.source %>images/',
      src: '**',
      dest: '<%=config.build %><%=config.assets %>images/'
    },

    assets: {
      expand: true,
      cwd: '<%=config.source %>customassets/',
      src: '**',
      dest: '<%=config.build %><%=config.assets %>/'
    },

    codeignighter: {
      cwd: '<%=config.ciroot %>',
      src: [
        '**',
        //ignore
        '!user_guide_src/**',
        '!build-release.sh',
        '!composer.json',
        '!contributing.md',
        '!DCO.txt',
        '!license.txt',
        '!phpdoc.dist.xml',
        '!readme.rst',
        '!tests/**',
      ],
      dot: true,
      mode: '0777',
      dest: '<%=config.build %>',
      expand: true
    },

    phpunitci: {
      cwd: 'phpunit/application/tests/',
      src: [
        '**',
        '!controllers/Welcome_test.php'
      ],
      dest: '<%=config.build %>application/tests',
      expand: true,
      mode: '0777'
    },

    ifx: {
      expand: true,
      cwd: 'ifx/source/ci/',
      src: ['**'],
      mode: '0777',
      dest: '<%=config.build %>'
    },

    citests: {
      cwd: 'tests/ci/',
      src: [
        '**',
      ],
      dest: '<%=config.build %>application/tests',
      expand: true,
      mode: '0777'
    },

    sourcephp: {
      expand: true,
      cwd: '<%=config.source %>ci/',
      src: ['**'],
      mode: '0777',
      dest: '<%=config.build %>'
    },

    ciconfig: {
      expand: true,
      cwd: '<%=config.source %>ci/application/ifx/setup/',
      src: ['**'],
      mode: '0777',
      dest: '<%=config.build %>application'
    }
  });

  grunt.config('jshint', {
    options: {
      curly: true,
      eqeqeq: true,
      eqnull: true,
      browser: true,
      //undef: true,
      //unused: true,
      debug: grunt.config('compress') === false,
      globals: {
        jQuery: true
      }
    },
    all: ['gruntfile.js',
      '<%=config.source %>js/**/*.js'
    ],
  });

  grunt.config('less', {
    run: {
      options: {
        compress: grunt.config('compress'),
        yuicompress: grunt.config('compress'),
        cleancss: grunt.config('compress'),
        plugins: [new(require('less-plugin-autoprefix'))({
          browsers: ["last 2 versions"]
        })],
        rootpath: ["/assets"]
      },
      files: {
        'temp/css/less.css': 'temp/compiled.less'
      }
    }
  });

  grunt.config('lesslint', {
    src: ["temp/compiled.less"],
    options: {
      csslint: {
        'universal-selector': false,
        'adjoining-classes': false
      }
    } //"<%= config.source %>less/**/*.less"]
  });

  grunt.config('modernizr', {
    build: {
      dest: 'temp/modernizr.js',
      crawl: false
    }
  });

  var php_directives = {
    'short_open_tag': 'On',
    'error_log': require('path').resolve('php-logs/error.log'),
    'log_errors': 1,
    'display_startup_errors': 1,
    'max_execution_time': 5,
    'memory_limit': '256M',
  };

  var debug_directives = {
    'xdebug.remote_enable': 1,
    'xdebug.remote_host': '127.0.0.1',
    'xdebug.remote_connect_back': 1,
    'xdebug.remote_port': 9000,
    'xdebug.remote_handler': 'dbgp',
    'xdebug.remote_mode': 'req',
    'xdebug.remote_autostart': true,
  };

  var server_directives = php_directives;
  var worker_directives = php_directives;

  if (process.env.debug_worker) {
    grunt.log.writeln('DEBUGGER: Worker enabled');
    worker_directives = Object.assign(worker_directives, debug_directives);
  } else {
    grunt.log.writeln('DEBUGGER: Server enabled');
    server_directives = Object.assign(server_directives, debug_directives);
  }

  var profiler_directives = {
    'xdebug.profiler_enable_trigger': 1,
    'xdebug.profiler_output_dir': require('path').resolve('php-debug-log/'),
  };

  if (grunt.option('profiler-main') === 'on') {
    grunt.log.writeln('PROFILER: Server enabled - ' + profiler_directives['xdebug.profiler_output_dir']);
    server_directives = Object.assign(server_directives, profiler_directives);
  }

  if (grunt.option('profiler-worker') === 'on') {
    grunt.log.writeln('PROFILER: Worker enabled - ' + profiler_directives['xdebug.profiler_output_dir']);
    worker_directives = Object.assign(server_directives, profiler_directives);
  }

  grunt.config('php', {
    server: {
      options: {
        hostname: '127.0.0.1',
        port: 9001,
        base: '<%=config.build %>',
        directives: server_directives
      }
    },
    worker: {
      options: {
        hostname: '127.0.0.1',
        port: 9002,
        base: '<%=config.build %>',
        directives: worker_directives
      }
    }
  });

  var testPath = '';
  if (grunt.option('unitpath')) {
    testPath = grunt.option('unitpath');
  }

  grunt.config('shell', {
    codeignighter: {
      command: 'git clone --branch master https://github.com/bcit-ci/CodeIgniter.git <%=config.ciroot %>',
    },
    ifx: {
      command: 'git clone --branch master https://github.com/Swift-Jr/ifx ifx',
    },
    phpunit_install: {
      command: 'curl https://phar.phpunit.de/phpunit-6.0.13.phar -o phpunit.phar; chmod +x phpunit.phar; sudo mv phpunit.phar /usr/local/bin/phpunit; phpunit --version;'
    },
    phpunit: {
      command: 'git clone --branch master https://github.com/kenjis/ci-phpunit-test.git <%=config.phpunitroot %>',
    },
    tests: {
      command: 'cd build/application/tests/; phpunit ' + testPath + ';',
    },
    tests_debug: {
      command: 'cd build/application/tests/; export XDEBUG_CONFIG="remote_host=localhost remote_enable=1 remote_autostart=1"; phpunit ' + testPath + ';',
    }
  });

  grunt.config('watch', {
    grunt: {
      files: ['gruntfile.js'],
      tasks: grunt.config('taskList')
    },

    css: {
      files: ['<%=config.source %>less/*.less',
        '<%=config.source %>less/**/*.less',
      ],
      tasks: grunt.config('onLESSChange'),
      options: {
        npspawn: true,
        livereload: true
      }
    },

    customassets: {
      files: ['<%=config.source %>customassets/**/*.*'],
      tasks: grunt.config('onAssetChange'),
      options: {
        npspawn: true,
        livereload: true
      }
    },

    js: {
      files: [
        '<%=config.source %>js/*.js',
        '<%=config.source %>js/**/*.js'
      ],
      tasks: grunt.config('onJSChange'),
      options: {
        npspawn: true,
        livereload: true
      }
    },

    php: {
      files: [
        '<%=config.source %>ci/**',
      ],
      tasks: grunt.config('onPHPChange'),
      options: {
        npspawn: true,
        livereload: true
      }
    }

  });

  //END TASK CONFIG
};
