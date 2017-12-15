# Application Philosophy

## The View Component

The view component mingles documents and elements into larger documents (and perhaps elements). It is responsible for coordinating modeled data into placeholder values into the template. It handles rendering, and before/after filtering of output

## The Data Component

The data component has two functions, the first is to provide a quick interface to connecting to a data store (probably a file in the `/data` directory), and the second is to act as a registry for finding and manipulating data objects (mapping, reduction, limiting, iterating).

## The Controller Component

Provides some default methods and enforces some abstract methods when setting up a new controller

## The Model Component

## The Request Component

The request component parses and determines the criteria necessary to take further action. It also accepts responsibility for delegating to the appropriate controller.

## The Response Component

## The Document Component

This framework is authored from an appreciation of the *Document Object Model*. As much as possible, those patterns are utilized, but in order to compose more complicated documents, the DOMDocument object is extended to provide more parsing and rendering capabilities.

## The Element Component

Like the document, this extends a DOM component, that gives traversal and querying a bit more power

## The Comment Component

This small component encapsulates two ideas. The first, is that hidden data should and will be meaningful. In that regard, comments in XML can be used to provide instruction—notable how and where to include additional templates. Likewise, comments serve as a container for logging messages.

Also enhances a DOM component