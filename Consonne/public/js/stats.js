
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
          'green',
          'orange',
          'violet'
        ]
    }],

    // These labels appear in the legend and in the tooltips when hovering different arcs
    labels: [
        '-6ans',
        '6-8ans',
        '9-11ans',
        '12-15ans',
        '16-17ans',
        '+18ans'
    ]
},

    // Configuration options go here
    options: {}
}
);
