(function ($) {
	$(function () {
		var EarningsReport = function (placeholder, data) {
	this.data = data;

	this.options = {
		xaxis: {
			mode: "time",
			timeBase: "milliseconds",
			autoScale: "none",
			min: data.startDate,
			max: data.endDate,
			timeformat: data.timeformat,
			tickSize: data.tickSize,
			alignTicksWithAxis: 1
		},
		yaxes: [
			{
				position: "left",
				min: 0,
				tickDecimals: 0,
				alignTicksWithAxis: 1
			},
			{
				position: "right",
				min: 0
			}
		],
		grid: {
			hoverable: true,
			borderColor: '#eee'
		},
		legend: {
			show: false
		}
	};

	function filterDataByDataType(data, highlighted) {
		var plotData = data.plotData,
			currencySymbol = data.currencySymbol,
			dataFilters = [],
			dataTypes = [],
			plot = [];

		jQuery('.mphb-chart-legend-item-checkbox:checked').each(function () {
			dataTypes.push(jQuery(this).parents('.mphb-chart-legend-item').data('datatype'));
		});

		jQuery('.mphb-data-filter-checkbox:checked').each(function () {
			dataFilters.push(jQuery(this).parents('li').data('datafilter'));
		});

		// Move highlighted plots to front
		if (highlighted) {
			var n = [];
			var toMove = [];
			plotData.forEach(function (series, ind) {
				if (series.dataType == highlighted) {
					n.push(ind);
					toMove.push(series);
				}
			});
			if (n) {
				plotData = plotData.filter(function (series, ind) {
					return !n.includes(ind);
				});
				toMove.forEach(function (series) {
					plotData.push(series);
				});
			}
		}

		for (var i = 0; i < plotData.length; i++) {
			plotDataType = plotData[i].dataType,
				plotDataFilter = plotData[i].dataFilter,
				color = plotData[i].color,
				showBars = plotData[i].plotType && plotData[i].plotType == 'bars',
				showDashes = plotData[i].plotType && plotData[i].plotType == 'dashes',
				barWidth = plotData[i].barWidth,
				dashLength = plotData[i].dashLength,
				d = plotData[i].plotArray,
				yaxis = showBars ? 1 : 2;
			lineWidth = 1;

			if (dataTypes.length <= 0 || !dataTypes.includes(plotDataType)
				|| dataFilters.length <= 0 || !dataFilters.includes(plotDataFilter)) {
				continue;
			}

			if (highlighted && plotDataType == highlighted) {
				lineWidth = 4;
			}

			plot.push(
				{
					symbol: currencySymbol,
					color: color,
					points: { show: !showBars, radius: 3, lineWidth: 1, fillColor: '#fff', fill: true },
					lines: { show: !showBars && !showDashes, lineWidth: lineWidth, fill: false },
					dashes: { show: showDashes, lineWidth: lineWidth, dashLength: dashLength },
					bars: { show: showBars, barWidth: barWidth, lineWidth: 0, fill: true, fillColor: color, align: 'center' },
					yaxis: yaxis,
					data: d
				}
			);
		}

		return plot;
	}

	function showTooltip(x, y, contents) {
		jQuery('<div class="mphb-tooltip">' + contents + '</div>')
			.css({
				left: x + 10,
				top: y - 13,
				borderRadius: 3,
				opacity: 0.95,
				position: "absolute"
			})
			.appendTo('body')
			.fadeIn(200);
	}

	function removeTooltip() {
		jQuery('.mphb-tooltip').stop().remove();
	}

	function drawPlot(options, plot) {
		jQuery.plot(placeholder, plot, options);
		initTooltips();
	}

	function initTooltips() {
		jQuery(placeholder).bind("plothover", function (event, pos, item) {
			removeTooltip();

			if (!pos.x || !(pos.y1 || pos.y2)) return;

			if (item) {
				var number = item.series.bars.show ? item.datapoint[1].toFixed(0) : item.datapoint[1].toFixed(2),
					symbol = !item.series.bars.show ? item.series.symbol : '',
					content = symbol + number;
				showTooltip(item.pageX, item.pageY, content);
			} else {
				removeTooltip();
			}
		});

		jQuery(placeholder).bind("plothovercleanup", function (event, pos, item) {
			removeTooltip();
		});
	}

	function clearClasses() {
		jQuery('.mphb-clicked').removeClass('mphb-clicked mphb-highlighted');
		jQuery('.mphb-highlighted').removeClass('mphb-clicked mphb-highlighted');
	}

	function highlightPlot(e) {
		jQuery(this).addClass('mphb-highlighted');

		var data = e.data[0],
			options = e.data[1],
			dataType = jQuery(this).data('datatype'),
			plot = filterDataByDataType(data, dataType);

		drawPlot(options, plot);
	}

	function unhighlightPlot(e) {
		jQuery(this).removeClass('mphb-highlighted');

		var data = e.data[0],
			options = e.data[1],
			plot = filterDataByDataType(data);

		drawPlot(options, plot);
	}

	function filterPlot(e) {
		var data = e.data[0],
			options = e.data[1],
			dataType = jQuery(this).parents('.mphb-chart-legend-item').data('datatype'),
			plot;

		plot = filterDataByDataType(data, dataType);
		drawPlot(options, plot);
	}

	jQuery('.mphb-chart-legend-item').on('mouseenter', [this.data, this.options], highlightPlot);
	jQuery('.mphb-chart-legend-item').on('mouseleave', [this.data, this.options], unhighlightPlot);
	jQuery('.mphb-chart-legend-item-checkbox').on('click', [this.data, this.options], filterPlot);
	jQuery('.mphb-data-filter-checkbox').on('click', [this.data, this.options], filterPlot);

	this.plot = function () {
		var plot = filterDataByDataType(this.data);
		drawPlot(this.options, plot);
	}
}

$('#mphb-dates-range-select').on('change', function () {
	if ($(this).val() == 'custom') {
		$('#mphb-dates-range-show').removeClass('mphb-invisible');
	} else {
		$('#mphb-dates-range-show').addClass('mphb-invisible');
	}
});

var data = JSON.parse(ReportData.data);

var g = new EarningsReport("#mphb-earnings-report", data);
g.plot();

	});
})(jQuery);