# Application Philosophy

From years of reading code and debugging projects, I've come to realize that Occam's Razor is a fun idea, but all too many authors of program code let these principles slide when trying to make more safe, more powerful, more widely useful projects. I've opted for an approach that aim's closer to the Razor, and let's be clear,  Razor's are not inherently safe (principle A), nor are they inherently powerful (Principle B) and they have limited uses (Principle C). Here goes...

### Principle A: safety

I've recently developed a fascination for small engines, particularly 2-stroke chainsaws (people get rid of them for next to nothing when they stop running well). A two-stroke engine can be temperamental, it may refuse to run or it may just run poorly. The thing is though, they are simple enough to be understood, and if you tinker long enough, they are usually fix-able. And the best part, *you don't even have to be that knowledgeable about engines*. Patience is the key here, and that is the dangerous part. To fix a problem with a simple machine, you have to understand the machine, its quirks, *its essence* and then one marvels at the edification thes mechanics can offer (yes, zen).  Mind, this is really only possible because such machines are small.

This application is small, it works, it has tight constraints, and I think it is  worth taking apart!

### Principle B: Powerful
Let me riff on the chainsaw for a few more seconds. Chainsaws cannot tow a boat down the expressway at 75mph, but, they are quite impressive in their intended context. Developers get carried away with scale, caching, performance, growth—all important things I suppose, but from my context, here is what my projects look like, and I'll exaggerate: maybe 100 users producing perhaps 20k pieces of 'content' (ever) and perhaps 150k pageviews per month. In the grand scheme of web development, this is a **very small** project. If one is always underwater trying to get things to run smoothly on a small project, then, analogy time, it has the appearance of spending unproductive energy on how to increase the speed from 6mph to 18mph while towing a toy boat with a monster truck down a gravel road.

Things can be powerful if they are used as intended, and making small websites on fast computers is, in my opinion, sawing logs—you just have to be willing to stack them.

### Principle C: Useful
Tools that are malleable, like code, should have their philosophy and should solve some problems, and then pipe down about things. They should be enhanced and manipulated by another author to become more useful to some other specific scenario, and the parts that are no longer useful in the original should be thrown away confidently, in order to maintain a piece of work that one mind can understand in an afternoon and some focus.
 
---

## The MVC File Components

### The View Class
The view component mingles documents and elements into larger documents (and perhaps elements). It is responsible for coordinating modeled data into placeholder values into the template. It handles rendering, and before/after filtering of output

- placeholder variables can be scoped/nested **during iteration**, ie. `<p>[[$$outer $inner:k]]</p>` would match data like `['outer'=>'..', 'inner => ['k' => '...']]` assuming the inner key was under iteration, the outer would get parsed again when the inner template finished rendering. Simply add more dollar signs and square brackets to escape the current scope for a higher one. Note, this is not something I typically do, but I find it necessary every now and then.

### The Controller Class

Provides some default methods and enforces some abstract methods when setting up a new controller

### The Model Class

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

### The Comment Class

This small component encapsulates two ideas. The first, is that hidden data should and will be meaningful. In that regard, comments in XML can be used to provide instruction—notable how and where to include additional templates. Likewise, comments serve as a container for logging messages.
