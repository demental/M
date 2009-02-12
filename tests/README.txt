The "tests/" folder currently contains files that are used for unit-testing purpose :
"DO_dist/" folder contains pristine DB_DataObject files that are used as a testing basis. 
When tests are run&setup, all the files are copied over the "DO/" folder.
As some tests write to some DO files (advgenerator and i18n migration process) we need to keep a copy as a starting point.

Therefore, in order to run the unit tests the DO/ folder must be writable by the script.