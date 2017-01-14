var margin = {top: 20, right: 20, bottom: 30, left: 40},
    width = 960 - margin.left - margin.right,
    height = 500 - margin.top - margin.bottom;

//var formatPercent = d3.format(".0%");

var x = d3.scale.ordinal()
    .rangeRoundBands([0, width], .1, 1);

var y = d3.scale.linear()
    .range([height, 0]);

var xAxis = d3.svg.axis()
    .scale(x)
    .orient("bottom");

var yAxis = d3.svg.axis()
    .scale(y)
    .orient("left");
    //.tickFormat(formatPercent);

var svg = d3.select("body").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
  .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

//appel de la donnée: horreur -> reprendre de manière à passer l’appel en argument de l’index
d3.tsv("code/viz/D3js/creation.tsv", function(error, data) {
  data.forEach(function(d) {
    d.livres = +d.livres;
  });

  x.domain(data.map(function(d) { return d.siècle; }));
  y.domain([0, d3.max(data, function(d) { return d.livres; })]);

  svg.append("g")
      .attr("class", "x axis")
      .attr("transform", "translate(0," + height + ")")
      .call(xAxis);

  svg.append("g")
      .attr("class", "y axis")
      .call(yAxis)
    .append("text")
      .attr("transform", "rotate(-90)")
      .attr("y", 6)
      .attr("dy", ".71em")
      .style("text-anchor", "end")
      .text("Livres");

  svg.selectAll(".bar")
      .data(data)
    .enter().append("rect")
      .attr("class", "bar")
      .attr("x", function(d) { return x(d.siècle); })
      .attr("width", x.rangeBand())
      .attr("y", function(d) { return y(d.livres); })
      .attr("height", function(d) { return height - y(d.livres); });

  d3.select("input").on("change", change);

  var sortTimeout = setTimeout(function() {
    d3.select("input").property("checked", true).each(change);
  }, 2000);

  function change() {
    clearTimeout(sortTimeout);

    // Copy-on-write since tweens are evaluated after a delay.
    var x0 = x.domain(data.sort(this.checked
        ? function(a, b) { return b.livres - a.livres; }
        //: function(a, b) { return d3.ascending(a.siècle, b.siècle); })
        : function(a, b) { return d3.sort(a.siècle, b.siècle); })
        .map(function(d) { return d.siècle; }))
        .copy();

    svg.selectAll(".bar")
        .sort(function(a, b) { return x0(a.siècle) - x0(b.siècle); });

    var transition = svg.transition().duration(750),
        delay = function(d, i) { return i * 50; };

    transition.selectAll(".bar")
        .delay(delay)
        .attr("x", function(d) { return x0(d.siècle); });

    transition.select(".x.axis")
        .call(xAxis)
      .selectAll("g")
        .delay(delay);
  }
});