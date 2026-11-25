const fs = require('fs');
const path = require('path');

function getStaticConfig() {
  // Franky stores the active site config in public/upload/<module>/core_config.
  // FRANKY_SITE_MODULE can select a site when the project has several modules.
  let moduleName = process.env.FRANKY_SITE_MODULE;
  if (!moduleName) {
    const siteMap = fs.readFileSync(path.join(__dirname, 'configure/data.php'), 'utf8');
    const match = siteMap.match(/=>\s*["']([^"']+)["']/);
    moduleName = match && match[1];
  }

  const configPath = moduleName && path.join(__dirname, 'public/upload', moduleName, 'core_config/core_config.php');
  if (!configPath || !fs.existsSync(configPath)) {
    throw new Error('No se encontró la configuración de Franky para el sitio. Define FRANKY_SITE_MODULE.');
  }

  const config = fs.readFileSync(configPath, 'utf8');
  const match = config.match(/['"]base\/debug\/cacheversion['"]\s*=>\s*['"]([^'"]+)['"]/);
  if (!match) {
    throw new Error(`No se encontró base/debug/cacheversion en ${configPath}`);
  }
  return { moduleName, cacheVersion: match[1] };
}

module.exports = function(grunt) {
  require('jit-grunt')(grunt);

  const staticRoot = `public/static/${getStaticConfig().cacheVersion}`;

  grunt.initConfig({
    less: {
      development: {
        options: {
          compress: true,
          yuicompress: true,
          sourceMap: true,
          optimization: 2
        },
        files: { // destination file and source file
          [`${staticRoot}/base/web/theme1/css/style.css`]: "modulos/base/web/theme1/less/style.less",
          [`${staticRoot}/base/web/default/css/style.css`]: "modulos/base/web/base/less/style.less",
          [`${staticRoot}/base/web/base/css/style.css`]: "modulos/base/web/base/less/style.less",
          [`${staticRoot}/base/web/base/css/grid.css`]: "modulos/base/web/base/less/grid.less",
          [`${staticRoot}/base/web/base/css/panel.css`]: "modulos/base/web/base/less/panel.less",
          [`${staticRoot}/base/web/default/css/panel.css`]: "modulos/base/web/default/less/panel.less",
          [`${staticRoot}/base/web/theme1/css/panel.css`]: "modulos/base/web/theme1/less/panel.less",
          [`${staticRoot}/ecommerce/web/css/cart.css`]: "modulos/ecommerce/web/less/cart.less",
          [`${staticRoot}/catalog/web/css/catalog.css`]: "modulos/catalog/web/less/catalog.less",
          [`${staticRoot}/blog/web/css/blog.css`]: "modulos/blog/web/less/blog.less",
          [`${staticRoot}/sliders/web/css/sliders.css`]: "modulos/sliders/web/less/sliders.less",
        }
      }
    },
    watch: {
      less: {
            files: [
            'modulos/base/web/base/less/*.less', 
            'modulos/base/web/default/less/*.less', 
            'modulos/base/web/theme1/less/*.less', 
            'modulos/ecommerce/web/less/*.less', 
            'modulos/catalog/web/less/*.less', 
            'modulos/blog/web/less/*.less', 
            'modulos/sliders/web/less/*.less'],
            tasks: ['less'],
            options: {
                livereload: true
            }
        }
    },
      browserSync: {
          dev: {
              bsFiles: {
                  src: [
                      `${staticRoot}/base/web/base/css/*.css`,
                      `${staticRoot}/base/web/default/css/*.css`,
                      `${staticRoot}/base/web/theme1/css/*.css`,
                      `${staticRoot}/catalog/web/css/*.css`,
                      `${staticRoot}/ecommerce/web/css/*.css`,
                      `${staticRoot}/blog/web/css/*.css`,
                      'modulos/base/diseno/*.phtml'
                  ]
              },
              options: {
                  watchTask: true,
                  proxy: "local.franky"
              }
          }
      }
  });
    grunt.loadNpmTasks('grunt-browser-sync');
    grunt.registerTask('default', ['less', 'browserSync', 'watch']);
};
