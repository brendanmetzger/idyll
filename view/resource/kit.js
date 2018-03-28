Event.prototype.theta = function () {
  var rect  = this.target.getBoundingClientRect();

  if (this.type.substring(0, 5) == 'touch') {
    var x = (this.touches[0].clientX - rect.left) - (rect.width / 2);
    var y = (rect.height / 2) - (this.touches[0].clientY - rect.top);
  } else {
    var x = (this.offsetX || this.layerX) - (rect.width / 2);
    var y = (rect.height / 2) - (this.offsetY || this.layerY);
  }
  var theta = Math.atan2(x, y);
  return theta < 0 ? Math.PI + theta : theta;
};

var SVG = function (node, width, height) {
  this.NS = Object.freeze({
    svg:   'http://www.w3.org/2000/svg',
    xlink: 'http://www.w3.org/1999/xlink'
  });
  this.width = width;
  this.height = height;
  this.element = this.createElement('svg', {
    'xmlns:xlink': this.NS.xlink, 'xmlns': this.NS.svg, 'version': 1.1, 'viewBox': `0 0 ${width} ${height}`
  }, node);
  
  this.point = this.element.createSVGPoint();
};


SVG.prototype.createElement = function (name, opt, parent) {
  var node = document.createElementNS(this.NS.svg, name);
  for (var key in opt) {
    if (key == "xlink:href") {
      node.setAttributeNS(this.NS.xlink, 'href', opt[key]);
    } else {
      node.setAttribute(key, opt[key]);
    }
  }
  return parent === null ? node : (parent || this.element).appendChild(node);
};

// Get point in global SVG space
SVG.prototype.cursorPoint = function (evt) {
  this.point.x = evt.clientX; 
  this.point.y = evt.clientY;
  return this.point.matrixTransform(this.element.getScreenCTM().inverse());
};

SVG.prototype.b64url = function (styles) {
  var clone = this.element.cloneNode(true);
  this.createElement('style', null, clone).textContent = styles;
  return `url(data:image/svg+xml;base64,${btoa(clone.outerHTML)})`;
};


// consider a 'Request' object, and a JSONP example using 'qwiki' (quick wiki) or weather