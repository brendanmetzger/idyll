# Application Philosophy

After years of authoring code and debugging projects—often projects which I did not write—I've come closer to my own version of an ideal framework, and the philosophy can be centered around my ideal is "do the absolute (absolute) minimum". As a general design philosophy this is [Occam's Razor](https://en.wikipedia.org/wiki/Occam's_razor)-esque, and the thinking goes, given two solutions to a problem or hypothesis, the simpler one is the better one . Many authors and designers of things omit such principles when trying to make more safe, more powerful, more widely useful products.

So, philosophically, I've opted for an approach that aim's closer to the law of parsimony; so let's be clear, razor's are not wholly safe (principle A), nor are they inherently powerful (Principle B) and they have limited uses (Principle C). Here goes an attempt to explain that those scary things are really kind of great...

### Principle A: Safety (or the necessity of being in control and assuming risk)

I have a fascination for small engines (particularly chainsaws). A two-stroke engine may become temperamental over time with heavy use and poor maintenance—it may start poorly, it may refuse to run entirely. However, given some inspection, they are able to be understood piece by piece, even without a manual. If you tinker long enough, often the fix is imminent if care is taken understand the function and necessity of each individual piece—which also means there are no extra, non-fundamental pieces to blear the edification process.

Patience is the key here—and lack of it is the dangerous part—skipping or misunderstanding components will not produce acceptable results. To fix a problem with a simple machine, first, understand the machine, then, adjust and revisit the worn pieces. I find that with this process, I'm able to make better decisions than I might out of the blue, because the pattern challenges me to exist within its bounds.  Again, this is only possible because such machines are small, you don't need a sprawling shop with tools galore, just a workbench and some patience.

### Principle B: Power
Let me riff on the chainsaw for a few more lines. Chainsaws cannot tow a yacht down the expressway, yet they are still impressive in their intended context. Humans (especially 'technologists') get carried away with growth, saturation and scale from the get-go. Perhaps those are important things (though also descriptive of cancers), but in my contexts, here is what a project may look like like: maybe 100 users producing perhaps 20k pieces of 'content' total, say, 150k visitors per month. In the grand scheme of web development, this is a very small project, yet it is still way bigger than an average website running wordpress or some other contraption. I'll spare another lengthy analogy, but line one should be dedicated to thinking about what will be towed. Hell, maybe that should always be a concern.

### Principle C: Useful
Tools, especially tools that are malleable (like those made from code) in my opinion, should deal with a typical quandary through disciplined, well-defined methods, and then pipe down about everything else. If you want the proverbial chainsaw to *insert task not involving cutting timber here*, then it would be foolish to think it can do that task without either fucking up the chainsaw or seriously rethinking some components.
 
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

### The Comment Class

This small component encapsulates two ideas. The first, is that hidden data should and will be meaningful. In that regard, comments in XML can be used to provide instruction—notable how and where to include additional templates. Likewise, comments serve as a container for logging messages.
