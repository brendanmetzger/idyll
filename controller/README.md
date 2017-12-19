# Controllers

Controllers are philosophically similar to most MVC implementations; classes that have methods called that are based on the URL, used to facilitate gathering data and generating output. Here are the particulars:

## Naming

Controllers that respond to requests should be prefixed with the request type. If the request is html, it will be either GET or POST, and if CLI, it would be a command a user typed into the console. That's what I generally use, but others could be invented and the app could be modified to facilitate whatever is desired.

## Arguments

Arguments to a function mirror the order of the path/query string for easy access to the variables. Use PHP's type declarations and default function arguments to write nice tight code—when the method has been chosen as a delegate it will do it's best to filter and deliver  the appropriate content.

## Access Controll

If you want anyone to be able to access a method, the method should be `public`. If the data requires login, change the method to `protected` and the method will be modified (see below) during delegation. A method set to private cannot be called outside of the class.

### Protected Functions

When a function is marked protected, the request delegation will implement a user-defined authentication process. If that fails, it will redirect to a login page. If it succeeds, it will push the user object as the first argument to the action method. Use duck typing to control access roles at that point if desired.

## Return Values

The method call should return a string or an object with toString capabilities. This will likely find it's way back to the response object, but that, like all things, can be determined by the author.

## Miscellaneous

The body of the method should set up a view object and gather modeled data to pass to the view. Views and models are rather profound in themselves (see those README files), so controller methods need not be too complicated. If configuration or methods are generic and sharable, consider adding them as a trait to the class.