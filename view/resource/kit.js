Event.prototype.theta = function () {
  var rect  = this.target.getBoundingClientRect();

  if (this.type.substring(0, 5) == 'touch') {
    var x = (this.touches[0].clientX - rect.left) - (rect.width / 2);
    var y = (rect.height / 2) - (this.touches[0].clientY - rect.top);
  } else {
    var x = (this.offsetX || this.layerX) - (rect.width / 2);
    var y = (rect.height / 2) - (this.offsetY || this.layerY);
  }
  var theta = Math.atan2(x, y) * (180 / Math.PI);
  return theta < 0 ? 360 + theta : theta;
};

var SVG = function (node, width, height) {
  const NS = {
    svg:   'http://www.w3.org/2000/svg',
    xlink: 'http://www.w3.org/1999/xlink'
  };
  
  this.element = this.createElement('svg', {
    'xmlns:xlink': NS.xlink, 'xmlns': NS.svg, 'version': 1.1, 'viewBox': `0 0 ${width} ${height}`
  }, node);
  
  this.point = this.element.createSVGPoint();
};


SVG.prototype.createElement = function (name, opt, parent) {
  var node = document.createElementNS(NS.svg, name);
  for (var key in opt) {
    if (key == "xlink:href") {
      node.setAttributeNS(NS.xlink, 'href', opt[key]);
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
  var wrapper     = document.createElement('div');
  var clone       = wrapper.appendChild(this.element.cloneNode(true));
  var style = this.createElement('style', null, clone);
      style.textContent = styles;
  return 'url(data:image/svg+xml;base64,'+btoa(wrapper.innerHTML)+')';
};