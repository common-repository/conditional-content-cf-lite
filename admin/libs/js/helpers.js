/**
 * Get the closest matching element up the DOM tree.
 */

var getClosest = function ( elem, selector ) {
  // Element.matches() polyfill
  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector
      || Element.prototype.mozMatchesSelector
      || Element.prototype.msMatchesSelector
      || Element.prototype.oMatchesSelector
      || Element.prototype.webkitMatchesSelector
      || function (s) {
        var matches = (this.document || this.ownerDocument).querySelectorAll(s);
        var i = matches.length;

        while (--i >= 0 && matches.item(i) !== this) {}
        return i > -1;
      };
  }

  // Get closest match
  for (; elem && elem !== document; elem = elem.parentNode) {
    if (elem.matches(selector)) {
      return elem;
    }
  }
  return null;
};

/**
 * Get all of an element's parent elements up the DOM tree
 */
var getParents = function (elem, selector) {
  var parents = [];

  if (!Element.prototype.matches) {
    Element.prototype.matches = Element.prototype.matchesSelector
      || Element.prototype.mozMatchesSelector
      || Element.prototype.msMatchesSelector
      || Element.prototype.oMatchesSelector
      || Element.prototype.webkitMatchesSelector
      || function (s) {
        var matches = (this.document || this.ownerDocument).querySelectorAll(s);
        var i = matches.length;
        
        while (--i >= 0 && matches.item(i) !== this) {}
        return i > -1;
      };
  }

  // Setup parents array

  // Get matching parent elements
  for (; elem && elem !== document; elem = elem.parentNode) {
    // Add matching parents to array
    if (selector) {
      if (elem.matches(selector)) {
        parents.push(elem);
      }
    } else {
      parents.push(elem);
    }
  }
  return parents;
};
