# Draft

An austere way to develop websites with source code authored and designed to be read as code rather than documentation. After mounting frustration with bundlers and packagers, this project intends—if only as an example—to show that dynamic websites with small-to-medium sizes of requirements can be built with a modest amount of programming.



## Goals and Principles
- Minimal Comments and explanations. No hopscotch of opening files and documentation to see what things mean.
- No dependancies
- No caching
- Template files 100% html/xml
  - markup is always valid
  - markup itself can be utilized as data
- Fully utilized Application (not implementation) to remain under 1000 SLOC. (hopefully well under)
- Avoid conditions/branching
- Things should break—either the code was improper, or the design was haphazard., and edification should be delivered with each fracture.
- only one echo statement in entire application... at the end

## Requirements

- php 7.1+
- DOM extension (should be enabled by default on mac osx version of php)
- apache by default (2.4 probably)

# Application Philosophy

I've opted for an approach that aim's closer to the law of parsimony, and this application strives to be tiny, useful, exacting, and effective.

Below is the outline of files and the classes therin.
 
---

## The MVC File Components

### The View Class
The view component mingles documents and elements into larger documents (and perhaps elements). It is responsible for coordinating modeled data into placeholder values into the template. It handles rendering, and before/after filtering of output

- placeholder variables can be scoped/nested **during iteration**, ie. `<p>[[$$outer $inner:k]]</p>` would match data like `['outer'=>'..', 'inner => ['k' => '...']]` assuming the inner key was under iteration, the outer would get parsed again when the inner template finished rendering. Simply add more dollar signs and square brackets to escape the current scope for a higher one. Note, this is not something I typically do, but I find it necessary every now and then.

### The Controller Class

Provides some default methods and enforces some abstract methods when setting up a new controller. The controller defines authentication procedures.

### The Model Class

The model, like all models, turns raw data into a useful thing. Each particular model will fixate on a protected property called the `$context`. In my version of data persistence, the context is always a `DOMElement`, and I have set things up accordingly (the framework could be rewritten to the particulars of a relational or object database, but according to application principles, I would revise core components to facilitate that).

---

## The IO File Components

### The Request Class

The request component parses and determines the criteria necessary to take further action. It also accepts responsibility for delegating to the appropriate controller.

### The Response Class

---

## The Data Component

The data component has two functions, the first is to provide a quick interface to connecting to a data store (probably a file in the `/data` directory), and the second is to act as a registry for finding and manipulating data objects (mapping, reduction, limiting, iterating).

The data component is based on facilities of the DOM components.

---

## The DOM File Components

This framework is authored from an appreciation of the *Document Object Model*. As much as possible, those patterns are utilized, but in order to compose more complicated documents, the DOMDocument object is extended to provide more parsing and rendering capabilities. **This is perhaps the most important facet to understand**. All `DOMNode` extended classes will have  `__invoke` and `__toString` magic methods available. They are used frequently as shorthands. The latter should be self explanatory, the former is how a node can have its data set, ie. `$node('this is nodeValue')`.

### The Element Class

Like the document, this extends a DOM component, that gives traversal and querying a bit more power

### The Attribute Class

### The Text Class

---

### The Locus File Components

### Place

### Calendar

### Weather



---

# Controllers

Controllers are philosophically similar to most MVC implementations; classes that have methods called that are based on the URL, used to facilitate gathering data and generating output. Here are the particulars:

## Naming

Controllers that respond to requests should be prefixed with the request type. If the request is from a web browser, it will be either GET or POST. If generating actions that respond via terminal, the methods should be prefixed with CLI.

## Arguments

Arguments to a function mirror the order of the path/query string for easy access to the variables. Use PHP's type declarations and default function arguments to gather the appropriate data for a method call. The arguments are manipulated in two situations: when the method is private, and when the method is prefixed with POST. If the method is private, during delegation, the appropriately authenticated class will be passed in as the first argument. During a POST execution, a `Data` object instance will be passed in as the first argument. If both, it is `Model` first and `Data` would be the second argument, then followed by the url arguments.

## Access Controll

If you want anyone to be able to access a method, the method should be `public`. If the data requires login, change the method to `protected` and the method will be modified (see below) during delegation. A method set to private cannot be called outside of the class, though I generally avoid adding private members to controller classes.

### Protected Functions

When a function is marked protected, the request delegation will implement a user-defined authentication process. If that fails, it will present a login page. If it succeeds, it will push the user object as the first argument to the action method. Use duck typing to control access roles at that point if desired, and that type will be used for authenticating calls to that method

## Return Values

The method call should return a `Document` or an object capable of producing one. This will likely find it's way back to the response object, but that can be determined by the author.

## Miscellaneous

In the most common implementation, the body of the method should set up a `View` object and gather `Model` data to apply. Views and models are much more profound than controllers, so whenever controller methods start becoming complicated, it is a strong indication of some refactoring necessary.

## Configuration

If certain properties or features are needed throughout a class, consider adding them as a trait, and then using that trait within each controller. There is no formula for how this should be done.

---

# Data

This is where you can store an entire websites worth of data. Moby dick is about 1.1Mb. It can be done.

## Format

As all data will be modeled into a structure represented by the Document Object Model, I feel that all data can be represented by the Document Object Model, or in XML. This format has the added benefit of allowing a Document Type Definition that should be used to ensure validity during transactions.

---
# Models

---

# View

This is my rewrite, where you see EXT's, I put in the files I might expect to generate with the framework, which would be: 

# Web Server

## Setup

1 Start an AWS Ubuntu instance, micro is fine.
2 Install Apache and PHP 7.1+
3 Get a certificate using [certbot](https://certbot.eff.org/), which will go through instructions on how to install certbot
 - note: configtest will fail if not running as root
 - crontab config I use is : 13 0,12 * * * /usr/bin/certbot renew -q, install as root
4 Add a repo to the server to store data in; [instructions](https://git-scm.com/book/en/v2/Git-on-the-Server-Setting-Up-the-Server)

## Virtual Host
`html|xml|css|js|svg|json|jpe?g|png`
```

RewriteEngine On
RewriteOptions Inherit
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^([A-z]*)\/?([A-z]*)\/?((?:[A-z0-9-:\/_*=]|\.[^A-z])*)(?:\.(EXT's))?$ index.php?_r_[]=$1&_r_[]=$2&_p_=$3&_e_=$4 [B,QSA,L]

```

