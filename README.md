# Draft

An austere way to develop websites with source code authored and designed to be read as code rather than documentation. After mounting frustration with bundlers and packagers, this project intends—if only as an example—to show that dynamic websites with small-to-medium sizes of requirements can be built with a modest amount of programming.



## Principles
- Minimal Comments and explanations. No hopscotch of opening files and documentation to see what things mean.
- No dependancies
- This framework is designed to operate un-cached (caching is easy and often leads to slop)
- Complete and non-negotiable separation of data/models and viems (templates are *exclusively* html/xml)
- Minimalist
- Perfect execution of DOM and output of valid markup
- markup itself can be utilized as data
- no component of the application will exceed ~150 lines, and no more than 5 files (max 1000 SLOC is a goal)
- Avoid conditions whenever possible
- Modeling and storing data is hard: the framework does not not model and store data—rather, it will elegantly deal with well designed, well structured data
- things should break often, and edification should be delivered with each fracture.


## Requirements

- php 7.1+
- DOM extension (should be enabled by default on mac osx version of php)
- apache by default (2.4 probably)