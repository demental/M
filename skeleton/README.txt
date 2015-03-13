The skeleton folder contains file structure to be used in a project.
The goal of the skeleton is to be used as a base for automatic code generation.

== Project structure explained ==

APP_ROOT/
  config.domain_name.php => domain-specific configuration options (database uri, documentRoot name, http uri, additional include paths....)
  public/    => the apache documentRoot
    .htaccess	=> URL rewriting
    index.php	=> Application dispatcher
    v1/	=> contains merged and minified web assets
      .htaccess	=> strong-caching directives according to Yahoo performance recommendations
	@see http://developer.yahoo.com/performance/rules.html#expires
	@see http://developer.yahoo.com/performance/rules.html#num_http
	@see http://developer.yahoo.com/performance/rules.html#minify
	@see http://developer.yahoo.com/performance/rules.html#gzip
      js/
      css/
  project/
    config.php	=> project-wide configuration options that's fired at application startup
    setup.php	=> project-wide configuration options encapsulated in a class that implements iSetup. is executed if cached data could not be retreived for the requested action. Tipically configures database connection, Mail settings ...
    assetsversion.php=> see below for explanation
    assets/	=> web assets (JS, CSS ....). Grouped by subfolders. These assets are merged by folders, minified and copied to a vXXX/ folder in the documentRoot each time the project is released. XXX is an auto-incremented variable that's written in assetsversion.php
      js/
        common/
          jquery.1.2.6.js	(example)
          ready.js	(example)
      css/
        common/
          reset-fonts-grids.css	(example)
          style.css	(example)
    lib/	=> placeholder for external libraries specifically used by this project
    _shared/
      templates/	=> contains templates that can be shared by all the project's applications but also mail templates and if used, pdf templates
        _mails/
        _pdf/
    lang/	=> lang files storing strings used in the application
      en.xml
      fr.xml
    app/	=> a project application (named "app". Most of the time there is a frontend and a backend in a project, so this one could be called "front" and the other "office")
      config.php	=> app-specific configuration options, fired at application startup
      setup.php	=> app-specific configuration options encapsulated in a class that implements iSetup. is executed if cached data could not be retreived for the requested action.
      cache/	=> application cache folder (contains generated HTML cache files and lang XML cache files)
        config/	=> cache for modules configuration options
      modules/	=> placeholder for Modules (controller layer). Each module is one file which name correspond to the module name (lower case)
        default.php
        default.conf.php => optional, contains module-specific configuration data (security, layout ....)
      templates/	=> placeholder for template files used by Modules (view layer). Each Module has its templates folder which name correspond to the module name (lower case). Each action has its template file which name is the action name (lower case) + some partial used for inclusion if necessary.
        default/
          index.php => template used when Module_Default::doExecIndex() is fired
          .........
      lib/	=> placeholder for external libraries specifically used by this application only
      tests/	=> placeholder for all this application's Module tests
    otherapp/	=> another project application (named "otherapp")
    models/	=> placeholder for DataObjects classes definitions (model layer)
    tests/	=> placeholder for DataObjects tests
       fixtures/  => SQL files containing test-related dumps.
         empty.sql=> auto-generated SQL file containing the project database structure only, used as a base for crud testing
       mytablename_tests.crud.php => auto-generated crud testing (used as a working base, the developer may add its own crud-related tests in these files, as they are generated only once they won't be overwritten by the generator)
       mytablename_tests.custom.php => custom DataObjects_Methods testing (no test is included, this is an empty class where the developer can write its own DO-related tests)
