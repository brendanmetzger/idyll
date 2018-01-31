app.prepare(function (evt) {
  document.body.addEventListener('dblclick', function(evt) {
    var target = evt.target;
    while(! target.matches('[data-template]')) target = target.parentNode;
    setTimeout(close.bind(window.open(`txmt://open?url=file://${target.dataset.template}`)), 10);
  });
});
