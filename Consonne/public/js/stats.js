
var ctx = document.getElementById('myChart').getContext('2d');


    var p3 = document.querySelector('.pegi3');
    var youngage = p3.dataset.pegi3;
    var p7 = document.querySelector('.pegi7');
    var young = p7.dataset.pegi7;
    var p9 = document.querySelector('.pegi9');
    var youngteen = p9.dataset.pegi9;
    var p12 = document.querySelector('.pegi12');
    var teen = p12.dataset.pegi12;
    var p16 = document.querySelector('.pegi16');
    var teenager = p16.dataset.pegi16;
    var p18 = document.querySelector('.pegi18');
    var adult = p18.dataset.pegi18;

var myChart = new Chart(ctx, {
    // The type of chart we want to create
    type: 'doughnut',

    // The data for our dataset
    data: {
    datasets: [{
        data: [youngage, young, youngteen, teen, teenager, adult],
        backgroundColor: [
          'red',
          'yellow',
          'blue',
          'brown',
          'orange',
          'violet'
        ]
    }],

    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [
        '-6ans',
        '-9ans',
        '-12ans',
        '-16ans',
        '-18ans',
        '+18ans'
    ]
},

    // Configuration options go here
    options: {}
}
);


var sta = document.getElementById('canvas').getContext('2d');
var chart = new Chart(sta, {
    // The type of chart we want to create
    type: 'doughnut',

    // The data for our dataset
    data: {
    datasets: [{
        data: [10, 20, 30],
        backgroundColor: [
          'pink',
          'brown',
          'orange'
        ]
    }],

    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [
        'pink',
        'brown',
        'orange'
    ]
},

    // Configuration options go here
    options: {}
}
);
