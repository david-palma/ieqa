/**
 * Project "IEQA - Indoor Environmental Quality Analyzer"
 * ICT laboratory - University of Udine
 * Academic year 2015
 *
 * File name: ieqa.js
 * Description: javascript functions
 * Author: David Palma
 * License:(CC BY-NC 4.0)
 *          This work is licensed under a Creative Commons
 *          Attribution 4.0 International License.
 *          Read the Legal Code(the full license) from the link
 *          http://creativecommons.org/licenses/by/4.0/legalcode
 */

/* Global variables */
var table,                // Data table
    sensorsTable,         // Data table in dashboard view
    dashboardChart,       // Graph in dashboard view
    dashboardGauge,       // Gauge in dashboard view
    series  = [],         // Data
    series1 = [],         // Data sent by board #0
    series2 = [],         // Data sent by board #1
    chart_s0,             // Graph of temperature
    chart_s1,             // Graph of humidity
    chart_s2,             // Graph of luminosity/brightness
    boxplot,              // Box-plot for statistics
    gauge0,               // Estimated quality for board #0
    gauge1,               // Estimated quality for board #1
    connection_status = [];
    delay = 3000;         // Update graphs, table etc. every "delay" ms

/* Function that sets options for the box-plot */
function setOptionsBoxplot()
{
   boxplot = new Highcharts.Chart
               ({
                  chart:
                  {
                     marginLeft: 80,
                     spacingTop: 15,
                     spacingBottom: 10,
                     renderTo: 'boxplot',
                     type: 'boxplot'
                  },
                  title: { text: '' },
                  credits: { enabled: false },
                  exporting: { enabled: false },
                  legend: { enabled: false },
                  plotOptions:
                  {
                     boxplot:
                     {
                        fillColor: '#F0F0E0',
                        lineWidth: 2,
                        medianColor: '#0C5DA5',
                        medianWidth: 3,
                        stemColor: '#A63400',
                        stemDashStyle: 'dot',
                        stemWidth: 1,
                        whiskerColor: '#3D9200',
                        whiskerLength: '20%',
                        whiskerWidth: 3
                     }
                  },
                  xAxis: { categories: ['board #0', 'board #1'] },
                  yAxis: { title: { text: null } },
                  series:
                  [{
                     tooltip: { headerFormat: '<em>{point.key}</em><br/>' },
                     data: []
                  }],
               });
}

/* Function that gets and sets json data for table view */
function setDataStatistics()
{
   // Get data for the selected parameter
   var par = getParameter();
   var min, max, avg, variance, dev_std, coeff_var, irq, unit = [], name = [];

   if(par == 0)
      document.getElementById("titleStatsTable").innerHTML = "Statistics Table - Temperature";
   else if(par == 1)
      document.getElementById("titleStatsTable").innerHTML = "Statistics Table - Relative humidity";
   else
      document.getElementById("titleStatsTable").innerHTML = "Statistics Table - Luminosity";

   unit[0] = "°C";
   unit[1] = "%";
   unit[2] = "lux";
   name[0] = "Temperature";
   name[1] = "Relative humidity";
   name[2] = "Luminosity";

   $.getJSON('http://192.168.43.100/ieqa/json_data_statistics/' + par, function(json_data)
   {
      // Get the statistics
      min       = json_data.table.min;
      max       = json_data.table.max;
      avg       = json_data.table.avg;
      variance  = json_data.table.variance;
      dev_std   = json_data.table.dev_std;
      coeff_var = json_data.table.coeff_var;
      irq       = json_data.table.irq;

      // Set data
      for(var i = 0; i < 2; i++)
      {
         if(max[i] >= min[i])
         {
            document.getElementById("data_min" + i).innerHTML       = min[i].toFixed(2) + " " + unit[par];
            document.getElementById("data_max" + i).innerHTML       = max[i].toFixed(2) + " " + unit[par];
            document.getElementById("data_avg" + i).innerHTML       = avg[i].toFixed(2) + " " + unit[par];
            document.getElementById("data_variance" + i).innerHTML  = variance[i].toFixed(2);
            document.getElementById("data_dev_std" + i).innerHTML   = dev_std[i].toFixed(2);
            document.getElementById("data_coeff_var" + i).innerHTML = coeff_var[i].toFixed(2) + " " + unit[1];
            document.getElementById("data_irq" + i).innerHTML       = irq[i].toFixed(2);
         }
         else
         {
            document.getElementById("data_min" + i).innerHTML       = 'Too few data';
            document.getElementById("data_max" + i).innerHTML       = 'Too few data';
            document.getElementById("data_avg" + i).innerHTML       = 'Too few data';
            document.getElementById("data_variance" + i).innerHTML  = 'Too few data';
            document.getElementById("data_dev_std" + i).innerHTML   = 'Too few data';
            document.getElementById("data_coeff_var" + i).innerHTML = 'Too few data';
            document.getElementById("data_irq" + i).innerHTML       = 'Too few data';
         }
      }

      // Set data to draw the boxplot
      boxplot.series[0].setData(json_data.boxplot[par]);
      boxplot.series[0].update({ tooltip: {valueSuffix: ' ' + unit[par]}, name: name[par] });
      boxplot.yAxis[0].update({ title: { enabled: true, text: name[par] + ' [' + unit[par] + ']' }, labels: { format: '{value} ' }});
   });
}

/* Function that sets options for all "spline" charts */
function setOptionsGraphs()
{
   Highcharts.setOptions
   ({
      global: { useUTC: false },
      chart:
      {
         marginLeft: 75,
         spacingTop: 15,
         spacingBottom: 10
      },
      title:
      {
         align: 'left',
         marginBottom: 3,
         x: 30,
         text: null
      },
      credits: { enabled: false },
      exporting: { enabled: false },
      legend:
      {
         align: 'right',
         verticalAlign: 'bottom',
         layout: 'horizontal',
         borderWidth: 0,
         borderRadius: 0,
         itemStyle:
         {
            color: '#808080',
            fontWeight: 'normal'
         },
         enabled: true
      },
      xAxis:
      {
         crosshair: true,
         type: 'datetime',
         labels: { format: '{value:%b %d - %H:%M}', rotation: -45, align: 'right' }
      },
      yAxis: { title: { text: null }},
      colors: ['#428BCA', '#DD4814'],
      plotOptions:
      {
         series:
         {
            marker:
            {
               enabled: false,
               states:
               {
                  hover:
                  {
                     enabled: true,
                     radius: 3
                  }
               }
            }
         }
      },
      series:
      [{
         data: [],
         type: 'spline',
         fillOpacity: 0.3
      },
      {
         data: [],
         type: 'spline',
         fillOpacity: 0.3
      }]
   });
}

/* Function that initializes graphs */
function initGraphs()
{
   // Create the charts
   chart_s0 = new Highcharts.Chart
               ({
                  chart:  { renderTo: 'chart_s0', zoomType: 'x' },
                  loading:
                  {
                     showDuration: 400,
                     hideDuration: 1600
                  },
                  title:  { text: 'Temperature' },
                  yAxis:  { labels: { format: '{value} ' + ' °C' }},
                  tooltip:
                  {
                     pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                     shadow: false,
                     valueSuffix: ' °C',
                     valueDecimals: 2
                  },
                  series: [{ data: [], name: 'Board #0' },{ data: [], name: 'Board #1' }]
               });

   chart_s1 = new Highcharts.Chart
               ({
                  chart: { renderTo: 'chart_s1', zoomType: 'x' },
                  loading:
                  {
                     showDuration: 400,
                     hideDuration: 1600
                  },
                  title: { text: 'Relative humidity' },
                  yAxis: { labels: { format: '{value} ' + ' %' }},
                  tooltip:
                  {
                     pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                     shadow: false,
                     valueSuffix: ' %',
                     valueDecimals: 2
                  },
                  series: [{ data: [], name: 'Board #0' },{ data: [], name: 'Board #1' }]
               });

   chart_s2 = new Highcharts.Chart
               ({
                  chart: { renderTo: 'chart_s2', zoomType: 'x' },
                  loading:
                  {
                     showDuration: 400,
                     hideDuration: 1600
                  },
                  title: { text: 'Luminosity' },
                  yAxis: { labels: { format: '{value} ' + ' lux' }},
                  tooltip:
                  {
                     pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                     shadow: false,
                     valueSuffix: ' lux',
                     valueDecimals: 2
                  },
                  series: [{ data: [], name: 'Board #0' },{ data: [], name: 'Board #1' }]
               });

   chart_s0.showLoading('Loading data...');
   chart_s1.showLoading('Loading data...');
   chart_s2.showLoading('Loading data...');
}

/* Function that enables or disables "make report" button */
function setButton()
{
   $.getJSON('http://192.168.43.100/ieqa/json_data_statistics/0', function(json_data)
   {
      var flag = 0;

      if((json_data.table.max[0] < json_data.table.min[0]) && (json_data.table.max[1] < json_data.table.min[1]))
         flag = 1;

      $.getJSON('http://192.168.43.100/ieqa/json_data_statistics/1', function(json_data)
      {
         if((json_data.table.max[0] < json_data.table.min[0]) && (json_data.table.max[1] < json_data.table.min[1]))
            flag = 1;

         $.getJSON('http://192.168.43.100/ieqa/json_data_statistics/2', function(json_data)
         {
            if((json_data.table.max[0] < json_data.table.min[0]) && (json_data.table.max[1] < json_data.table.min[1]))
               flag = 1;

            if(flag)
               $('button').attr('disabled', true);
            else
               $('button').attr('disabled', false);
         });
      });
   });
}

/* Function that gets and sets json data for graphs view */
function setDataGraphs()
{
   // Get data for graphs
   $.getJSON('http://192.168.43.100/ieqa/json_data_graphs/' + '0', function(json_data)
   {
      $.each(json_data.datasets, function(i, dataset)
      {
         // Add X values to "data" values
         dataset.data = Highcharts.map(dataset.data, function(val, j)
         {
            return [json_data.xData[j] * 1000, val];
         });

         // Store the data into the arrays
         series1[i] = dataset.data;
      });
   });

   $.getJSON('http://192.168.43.100/ieqa/json_data_graphs/' + '1', function(json_data)
   {
      $.each(json_data.datasets, function(i, dataset)
      {
         // Add X values to "data" values
         dataset.data = Highcharts.map(dataset.data, function(val, j)
         {
            return [json_data.xData[j] * 1000, val];
         });

         // Store the data into the arrays
         series2[i] = dataset.data;
      });
   });

   chart_s0.series[0].setData(series1[0]);
   chart_s0.series[1].setData(series2[0]);
   chart_s1.series[0].setData(series1[1]);
   chart_s1.series[1].setData(series2[1]);
   chart_s2.series[0].setData(series1[2]);
   chart_s2.series[1].setData(series2[2]);
}

/* Function that gets and sets json data for graphs view */
function setDataGraphs2()
{
   var name  = [],
       unit  = [],
       chart = getChart();
   unit[0] = " °C";
   unit[1] = " %";
   unit[2] = " lux";
   name[0] = "Temperature";
   name[1] = "Relative humidity";
   name[2] = "Luminosity";

   // Get data for graphs
   $.getJSON('http://192.168.43.100/ieqa/json_data_graphs/' + '0', function(json_data)
   {
      $.each(json_data.datasets, function(i, dataset)
      {
         // Add X values to "data" values
         dataset.data = Highcharts.map(dataset.data, function(val, j)
         {
            return [json_data.xData[j] * 1000, val];
         });

         // Store the data into the arrays
         series1[i] = dataset.data;
      });
   });

   $.getJSON('http://192.168.43.100/ieqa/json_data_graphs/' + '1', function(json_data)
   {
      $.each(json_data.datasets, function(i, dataset)
      {
         // Add X values to "data" values
         dataset.data = Highcharts.map(dataset.data, function(val, j)
         {
            return [json_data.xData[j] * 1000, val];
         });

         // Store the data into the arrays
         series2[i] = dataset.data;
      });
   });

   // Set data for selected chart
   dashboardChart.series[0].setData(series1[chart]);
   dashboardChart.series[1].setData(series2[chart]);
   dashboardChart.series[0].update({ tooltip: {valueSuffix: ' ' + unit[chart]} });
   dashboardChart.series[1].update({ tooltip: {valueSuffix: ' ' + unit[chart]} });
   dashboardChart.yAxis[0].update({ title: { enabled: false }, labels: { format: '{value} ' + unit[chart] }});
}

/* Function that initializes the dashboard graphs */
function initDashboard()
{
   // Get data for the selected board
   var board = getBoard();
   document.getElementById("titleDataTable").innerHTML = "Data Table Board &#35;" + board;
   
   sensorsTable = $('#sensorsTable').DataTable
                  ({
                     "dom":           "rtip",
                     "scrollY":        "45vh",
                     "scrollX":        true,
                     "scrollCollapse": true,
                     "paging":         false,
                     "data":           null,
                     "columns":
                     [
                        { "data": "id"        },
                        { "data": "timestamp" },
                        { "data": "s0"        },
                        { "data": "s1"        },
                        { "data": "s2"        }
                     ]
                  });

   // Create the charts
   dashboardChart = new Highcharts.Chart
                     ({
                        chart: { renderTo: 'dashboardChart' },
                        loading:
                        {
                           showDuration: 400,
                           hideDuration: 1000
                        },
                        title: { text: '' },
                        tooltip:
                        {
                           pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b><br/>',
                           shadow: false,
                           valueSuffix: '',
                           valueDecimals: 2
                        },
                        series: [{ data: [], name: 'Board #0' },{ data: [], name: 'Board #1' }]
                     });

   dashboardGauge = new Highcharts.Chart
                     ({
                        chart:
                        {
                           type: 'gauge',
                           renderTo: 'gauge',
                           plotBackgroundImage: null,
                           plotBackgroundColor: null,
                           plotBorderWidth: 0,
                           plotShadow: false,
                           spacingTop: 0,
                           spacingLeft: 0,
                           spacingRight: 0,
                           spacingBottom: 0
                        },
                        loading:
                        {
                           showDuration: 400,
                           hideDuration: 1000
                        },
                        title: null,
                        pane:
                        {
                           background: null,
                           startAngle: -90,
                           endAngle: 90,
                           center: ["33%","75%"],
                           size: '125%'
                        },
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        plotOptions:
                        {
                           gauge:
                           {
                              dataLabels:
                              {
                                 enabled: true,
                                 crop: false,
                                 overflow: 'none',
                                 useHTML: true
                              }
                           }
                        },
                        yAxis:
                        {
                           min: 0,
                           max: 100,
                           minorTickWidth: null,
                           minorTickLength: null,
                           title:
                           {
                              enabled: true,
                              y: -75,
                              text: ''
                           },
                           labels: { steps: 2 },
                           plotBands:
                           [{
                              from: 0,
                              to: 25,
                              color: '#DF5353' // Green
                           },
                           {
                              from: 25,
                              to: 75,
                              color: '#DDDF0D' // yellow
                           },
                           {
                              from: 75,
                              to: 100,
                              color: '#55BF3B' // Red
                           }],
                        },
                        series: [{}]
                     });

   dashboardChart.showLoading('Loading data...');
   dashboardGauge.showLoading('Loading data...');
}

/* Function that gets and sets json data for dashboard view */
function setDataDashboard()
{
   // Get data for the selected board
   var board = getBoard();
   document.getElementById("titleDataTable").innerHTML = "Data Table Board &#35;" + board;

   $.get('http://192.168.43.100/ieqa/data_available/' + board, function(data)
   {
      if(data == 1)
      {
         $.getJSON('http://192.168.43.100/ieqa/json_data_table/' + board, function(json_data)
         {
            sensorsTable.clear();
            sensorsTable.rows.add(json_data);
            sensorsTable.draw(false);
         });
      }
      else
      {
         sensorsTable.clear();
         sensorsTable.draw(false);
      }
   });

   
   // Set gauge for the selected board and update data
   setGauges();

   // Set data on the graphs
   setDataGraphs2();
}

function setGauges()
{
   // Get the board selected
   var board = getBoard();
   dashboardGauge.yAxis[0].update({ title: { enabled: true, y: -75, text: "Board #" + board } });

   $.get('http://192.168.43.100/ieqa/data_available/' + board, function(data)
   {
      if(data == 1)
      {
         // Get data for graphs
         $.getJSON('http://192.168.43.100/ieqa/json_data_graphs/' + board, function(json_data)
         {
            $.each(json_data.datasets, function(i, dataset)
            {
               // Add X values to "data" values
               dataset.data = Highcharts.map(dataset.data, function(val, j)
               {
                  return [json_data.xData[j] * 1000, val];
               });

               // Store the data into the arrays
               series[i] = dataset.data;
            });

            // Get the latest data of temperature, relative humidity and luminosity for the selected board
            var last_t   = series[0][series[0].length - 1][1],
                last_rh  = series[1][series[1].length - 1][1],
                last_lum = series[2][series[2].length - 1][1],
                Index    = [];  // array of index values

            // Computation of Heat Index
            Index[0] = HeatIndex(last_t, last_rh);

            // Computation of the Temperature-Humidity Index
            Index[1] = THI(last_t, last_rh);

            // Computation of the Luminosity/Brightness Index
            Index[2] = LBI(last_lum);

            // Computation of the total quality index
            Index[1] = (Index[1] > 35) ? 35 : ((Index[1] < 0) ? 0 : Index[0]);
            Index[3] = (1 - Math.abs(Index[1] - 17.5)/17.5) * 100;
            Index[3] = 0.8*(Index[3] > 100 ? 100 : (Index[3] < 0 ? 0 : Index[3])) + 0.2*Index[2];
            Index[3] = Math.round(Index[3] * 100)/100;
            dashboardGauge.series[0].setData([Index[3]]);

            document.getElementById("data_Tc").innerHTML  = last_t.toFixed(2)    + ' &deg; C';
            document.getElementById("data_RH").innerHTML  = last_rh.toFixed(2)   + ' &#37;';
            document.getElementById("data_lux").innerHTML = last_lum.toFixed(2)  + ' lux';
            document.getElementById("data_QI").innerHTML  = Index[3].toFixed(2)  + ' &#37;';
         });
      }
      else
      {
         dashboardGauge.series[0].setData(0);
         document.getElementById("data_Tc").innerHTML  = 'no data';
         document.getElementById("data_RH").innerHTML  = 'no data';
         document.getElementById("data_lux").innerHTML = 'no data';
         document.getElementById("data_QI").innerHTML  = 'no data';
      }
   });
}

/* Set the innerHTML field of the connection status */
function setStatusBoards()
{
   $.get('http://192.168.43.100/ieqa/ping/' + '192.168.43.10', function(data, status)
   {
      connection_status[0] = data;
   });

   $.get('http://192.168.43.100/ieqa/ping/' + '192.168.43.20', function(data, status)
   {
      connection_status[1] = data;
   });

   var status0 = document.getElementById("statusBoard0");
   var status1 = document.getElementById("statusBoard1");

   if(connection_status[0] == 1)
   {
      status0.innerHTML = "ONLINE";
      status0.style.color = "#22CC22";
   }
   else
   {
      status0.innerHTML = "OFFLINE";
      status0.style.color = "#FF0000";
   }

   if(connection_status[1] == 1)
   {
      status1.innerHTML = "ONLINE";
      status1.style.color = "#22CC22";
   }
   else
   {
      status1.innerHTML = "OFFLINE";
      status1.style.color = "#FF0000";
   }
}

/**
 * Computation of Heat Index given the ambient dry-bulb temperature in Celsius
 * (converted in Fahrenheit) and the relative humidity in percentage(from 0 to 100)
 */
function HeatIndex(T, RH)
{
   // Celsius to Fahrenheit conversion
   var Tf = T*1.8 + 32;

   // Constants for equation that is within 3 degrees of the NWS master table
   // RH: 0-80%
   // Tf: 70-115 °F
   // HI < 150 °F
   var c1 =  0.363445176,
       c2 =  0.988622465,
       c3 =  4.777114035,
       c4 = -0.114037667,
       c5 = -0.000850208,
       c6 = -0.020716198,
       c7 =  0.000687678,
       c8 =  0.000274954,
       c9 =  0.000000000;

   var HI_Fahrenheit = c1 + c2*Tf + c3*RH + c4*Tf*RH + c5*Tf*Tf + c6*RH*RH + c7*Tf*Tf*RH + c8*Tf*RH*RH + c9*Tf*Tf*RH*RH;
   return(HI_Fahrenheit - 32)/1.8; // Converted in Celsius
}

/**
 * Computation of Temperature–Humidity Index(THI) given the ambient dry-bulb temperature
 * in Celsius and the relative humidity in percentage(from 0 to 100)
 */
function THI(T, RH)
{
   var temp = (0.55 - 0.0055 * RH) *(T - 14.5);
   return(T - temp);
}

/* Computation of Luminosity-Brightness Index given the ambient luminosity in lux */
function LBI(L)
{
   var perfectLum = 500; // UNI EN 12464
   var y = L * (100 / perfectLum);
   return((y > 100) ? 100 : y);
}

/* Selected chart */
function getChart()
{
   return $("#selectChart option:selected").val();
}

/* Selected board */
function getBoard()
{
   return $("#selectBoard option:selected").val();
}

/* Selected board */
function getParameter()
{
   return $("#selectParameter option:selected").val();
}

/* Apply and show result of query applied to empty the DB */
$('#applyEmptyDB').click(function()
{
   var DBstatus = document.getElementById("message");

   $.post('http://192.168.43.100/ieqa/empty_table', function(data, status)
   {
      if(data == 1)
      {
         DBstatus.innerHTML = "<i>SQL query has been executed successfully</i>";
         DBstatus.style.color = "#22CC22";
      }
      else
      {
         DBstatus.innerHTML = "<i>SQL query has not been executed because the table is already empty</i>";
         DBstatus.style.color = "#FF0000";
      }

      $('#message').fadeIn(1000).delay(3000).fadeOut(1000);
   });
});

/* Apply and result of query applied to edit the sampling time */
$('#applySamplingTime').click(function()
{
   var samp_time = $('#samplingTime').val();
   var b0        = document.getElementById("b0").checked ? 1 : 0;
   var b1        = document.getElementById("b1").checked ? 1 : 0;
   var DBstatus  = document.getElementById("message");

   $.post('http://192.168.43.100/ieqa/edit_samp_time', { samp_time: samp_time, b0: b0, b1: b1 }, function(data, status)
   {
      if(data == 1)
      {
         DBstatus.innerHTML = "<i>SQL query has been executed successfully</i>";
         DBstatus.style.color = "#22CC22";
      }
      else
      {
         DBstatus.innerHTML = "<i>SQL query has not been executed because no base station has been selected</i>";
         DBstatus.style.color = "#FF0000";
      }

      $('#message').fadeIn(1000).delay(3000).fadeOut(1000);
   });
});

/* Apply and result of query applied to edit the threshold values */
$('#applyThresholdValues').click(function()
{
   var t0       = $('#minTemp').val();
   var t1       = $('#maxTemp').val();
   var t2       = $('#maxRH').val();
   var t3       = $('#minLux').val();
   var b0       = document.getElementById("b2").checked ? 1 : 0;
   var b1       = document.getElementById("b3").checked ? 1 : 0;
   var DBstatus = document.getElementById("message");

   $.post('http://192.168.43.100/ieqa/edit_threshold_values', { t0: t0, t1: t1, t2: t2, t3: t3, b0: b0, b1: b1 }, function(data, status)
   {
      if(data == 1)
      {
         DBstatus.innerHTML = "<i>SQL query has been executed successfully</i>";
         DBstatus.style.color = "#22CC22";
      }
      else
      {
         DBstatus.innerHTML = "<i>SQL query has not been executed because no base station has been selected</i>";
         DBstatus.style.color = "#FF0000";
      }

      $('#message').fadeIn(1000).delay(3000).fadeOut(1000);
   });
});

/* Only if the document is completly loaded */
$(document).ready(function()
{
   if($('div').is('.Statistics'))
   {
      setOptionsBoxplot();    // Set options for the box plot chart
      setDataStatistics();    // Set data for the first time (no wait)
      setInterval(function()
      {
         setDataStatistics(); // Set (update) data every "delay" seconds
      }, delay);
   }
	else if($('div').is('.Graphs'))
   {
      setOptionsGraphs();     // Set the common options for all charts
      initGraphs();           // Initialize the charts
      setDataGraphs();        // Set data for the first time (no wait)
      setTimeout(function()
      {
         chart_s0.hideLoading();
         chart_s1.hideLoading();
         chart_s2.hideLoading();
      }, delay);
      setInterval(function()
      {
         setDataGraphs();     // Set (update) data every "delay" seconds
      }, delay);
   }
	else if($('div').is('.Dashboard'))
   {
      setStatusBoards();      // Set the status of the boards
      setOptionsGraphs();     // Set the common options for all charts
      initDashboard();        // Initialize the charts and table
      setDataDashboard();     // Set data for the first time (no wait)
      setTimeout(function()
      {
         dashboardChart.hideLoading();
         dashboardGauge.hideLoading();
      }, delay);
      var flag = 1;
      setInterval(function()
      {
         setDataDashboard();  // Set (update) data every "delay" seconds
         if(flag === 1)
            setStatusBoards();   // Status boards
         flag = flag * (-1);
      }, delay);
   }
   else if($('div').is('.Report'))
   {
      setButton();  // Set (update) button status (only on refresh)
   }
});

// Initialization of the gmaps
/* function initialize()
{
   var uniud = new google.maps.LatLng(46.0807114,13.2115919); // position of DIEG

   var mapOptions =
   {
      backgroundColor: '#B3D1FF',
      center: uniud,
      keyboardShortcuts: false,
      mapTypeControl: true,
      mapTypeControlOptions: { style:google.maps.MapTypeControlStyle.DROPDOWN_MENU },
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      rotateControl: true,
      tilt: 40, // Angle of incidence of the map
      zoom: 15
   };

   // Create a new map object
   var map = new google.maps.Map(document.getElementById("map"), mapOptions);

   var marker = new google.maps.Marker(
   {
      map: map,
      position: uniud,
      title: "DIEG",
      animation: google.maps.Animation.DROP  // Set marker to drop
   });

   var contentString = '<p style="text-align: center;"><b> You can find us here!</b><br>' +
                       'Dept. of Electrical, Menagement and Mechanical Engineering<br>' +
                       'University of Udine<br>' +
                       'Via delle Scienze 206, 33100 UD - Italy</p>';

   // Create info boxes for the marker
   var infoWindow = new google.maps.InfoWindow(
   {
      content: contentString
   });

   // add action event so the info windows will be shown when the marker is clicked
   google.maps.event.addListener(marker, 'click', function()
   {
      infoWindow.open(map,marker);
   });
} */
