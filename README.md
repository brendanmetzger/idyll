# Draft

An austere way to develop websites with source code authored and designed to be read as code rather than documentation. After mounting frustration with bundlers and packagers, this project intends—if only as an example—to show that dynamic websites with small-to-medium sizes of requirements can be built with a modest amount of programming.

## Principles
- Caching is easy, and it's a crutch. This framework is designed to operate un-cached
- Complete and non-negotiable separation of data/models and views
- Minimalist (often means convention over configuration)
- Perfect execution of DOM and output of valid markup
- markup itself can be utilized as data
- no component of the application will exceed 100 lines, and no more than 10 components (max 1000 SLOC)
- conditions are often avoidable
- build to function on latest version of php bundled in mac os (7.1)
