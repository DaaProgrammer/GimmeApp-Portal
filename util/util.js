import {toTitleCase,shortenText} from './stringManipulation.js';
import {escapeHtml,unescapeHtml} from './jsonManipulation.js'

window.toTitleCase = toTitleCase;
window.shortenText = shortenText;
window.escapeHtml = escapeHtml;
window.unescapeHtml = unescapeHtml;

// Usage in HTML file
// <script src="path/to/util/util.js"></script>
// <script>
//     let title = window.toTitleCase("hello_world");
//     console.log(title); // Outputs: "Hello World"
// </script>
