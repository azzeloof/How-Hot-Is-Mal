const temperatureCtx = document.getElementById('temperatureChart').getContext('2d');
const pressureCtx = document.getElementById('pressureChart').getContext('2d');
const humidityCtx = document.getElementById('humidityChart').getContext('2d');
const statusDiv = document.getElementById('status');
const lastUpdatedDiv = document.getElementById('lastUpdated');
const currentTempDiv = document.getElementById('currentTemp');

let temperatureChart = null;
let pressureChart = null;
let humidityChart = null;


async function updateChartData() {
    try {
        // Only show "Updating..." if the charts already exist.
        if (temperatureChart) {
            statusDiv.textContent = 'Updating data...';
        }
        var window = document.getElementById('window').value;
        //console.log(`/get_data.php?window=${window}`);
        const response = await fetch(`/get_data.php?window=${window}`);
        
        if (!response.ok) {
            throw new Error(`Network response was not ok (status: ${response.status})`);
        }
        //console.log(response);

        const apiResponse = await response.json();

        if (apiResponse.error) {
            throw new Error(`API Error: ${apiResponse.error}`);
        }
        
        // If the chart hasn't been created yet, initialize it.
        if (!temperatureChart) {
            temperatureChart = initializeChart(apiResponse, temperatureCtx, 'Temperature');
        } else {
            // Otherwise, just update the existing chart's data.
            temperatureChart.data.labels = apiResponse.labels;
            temperatureChart.data.datasets[0].data = apiResponse.data['temperature'];
            temperatureChart.update(); // Redraw the chart
        }

        if (!pressureChart) {
            pressureChart = initializeChart(apiResponse, pressureCtx, 'Pressure');
        } else {
            // Otherwise, just update the existing chart's data.
            pressureChart.data.labels = apiResponse.labels;
            pressureChart.data.datasets[0].data = apiResponse.data['pressure'];
            pressureChart.update(); // Redraw the chart
        }

        if (!humidityChart) {
            humidityChart = initializeChart(apiResponse, humidityCtx, 'Humidity');
        } else {
            // Otherwise, just update the existing chart's data.
            humidityChart.data.labels = apiResponse.labels;
            humidityChart.data.datasets[0].data = apiResponse.data['humidity'];
            humidityChart.update(); // Redraw the chart
        }

        const now = new Date();
        const lastSampleTime = apiResponse.labels[apiResponse.labels.length - 1];
        lastUpdatedDiv.textContent = lastSampleTime;
        statusDiv.textContent = 'Last updated ' + now.toLocaleTimeString();
        currentTempDiv.textContent = parseFloat(apiResponse.data['temperature'][apiResponse.data['temperature'].length - 1]).toFixed(1);
    } catch (error) {
        console.error('Failed to fetch chart data:', error);
        statusDiv.textContent = `Error: ${error.message}`;
        // Hide the canvas if there's an error
        document.getElementById('temperatureChart').style.display = 'none';
        document.getElementById('pressureChart').style.display = 'none';
        document.getElementById('humidityChart').style.display = 'none';
    }
}

function initializeChart(apiData, ctx, name) {
    // Determine the correct unit for the y-axis based on the chart name
    let yAxisUnit = '';
    switch(name.toLowerCase()) {
        case 'temperature':
            yAxisUnit = '°F';
            break;
        case 'pressure':
            yAxisUnit = ' kPa';
            break;
        case 'humidity':
            yAxisUnit = '%';
            break;
    }

    const dataForChart = apiData.data[name.toLowerCase()];

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: apiData.labels,
            datasets: [{
                label: name,
                data: dataForChart, // Use the specific data array
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                pointRadius: 4,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: false,
                    ticks: { 
                        callback: (value) => value + yAxisUnit
                    }
                },
                x: {
                    ticks: { maxRotation: 70, minRotation: 45 }
                }
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `${context.dataset.label || ''}: ${context.parsed.y.toFixed(2)}${yAxisUnit}`
                    }
                }
            }
        }
    });
}


// Fetch data immediately when the page loads.
document.addEventListener('DOMContentLoaded', updateChartData);

// Set an interval to call the update function every 2 minutes
setInterval(updateChartData, 120000);