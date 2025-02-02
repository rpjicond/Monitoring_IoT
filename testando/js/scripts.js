// SIDEBAR TOGGLE

let sidebarOpen = false;
const sidebar = document.getElementById('sidebar');

function openSidebar() {
  if (!sidebarOpen) {
    sidebar.classList.add('sidebar-responsive');
    sidebarOpen = true;
  }
}

function closeSidebar() {
  if (sidebarOpen) {
    sidebar.classList.remove('sidebar-responsive');
    sidebarOpen = false;
  }
}


// Chamar a função para carregar os dados do arquivo JSON e atualizar o gráfico
//carregarDadosGrafico();
// Função para gerar cores diferentes
// Função para gerar cores diferentes
function gerarCoresDiferentes(numCores) {
  const cores = [];
  for (let i = 0; i < numCores; i++) {
    let cor;
    do {
      cor = '#' + Math.floor(Math.random()*16777215).toString(16);
    } while (cores.includes(cor));
    cores.push(cor);
  }
  return cores;
}

// Variável para armazenar o número de curvas
let numCurvas;

fetch('http://localhost/Jiconda_IoT/testando/historique/cherche.php')
    .then(response => response.json())
    .then(data => {
      const numArquivos = data.numArquivos;
      const nomesArquivos = data.nomesArquivos.split(', '); // Dividir a string em uma lista de nomes de arquivo
      numCurvas = numArquivos;

      console.log(`Número de arquivos no diretório: ${numArquivos}`);
      console.log('Nomes dos arquivos:', nomesArquivos);


      // Função para carregar dados de um arquivo JSON
      function carregarDadosArquivo(nomeArquivo) {
        return fetch(`http://localhost/Jiconda_IoT/testando/historique/modules_historique/${nomeArquivo}`)
            .then(response => response.json());
      }

      // Array de Promises para carregar dados de todos os arquivos
      const promessasCarregamento = nomesArquivos.map(carregarDadosArquivo);

      // Quando todas as Promises forem resolvidas, teremos os dados de todos os arquivos
      Promise.all(promessasCarregamento)
          .then(dadosArquivos => {
            // Aqui você tem os dados de todos os arquivos
            console.log('Dados dos arquivos:', dadosArquivos);

            // Agora você pode processar esses dados e atualizar o gráfico
            const cores = gerarCoresDiferentes(numCurvas);
            const series = [];

            // Iterar sobre os dados dos arquivos e criar séries para o gráfico
            dadosArquivos.forEach((dados, index) => {
              const numericValues = dados.map(data => {
                // Verificar se o valor é humidity, temperature ou pressure
                if (data.value.humidity !== undefined) {
                  return parseFloat(data.value.humidity);
                } else if (data.value.temperature !== undefined) {
                  return parseFloat(data.value.temperature);
                } else if (data.value.pressure !== undefined) {
                  return parseFloat(data.value.pressure);
                } else {
                  return null;
                }
              }).filter(value => value !== null);

              series.push({
                name: nomesArquivos[index].slice(0,-5),
                data: numericValues,
                color: cores[index], // Atribuir uma cor diferente
              });
            });

            // Atualizar as séries do gráfico
            areaChart.updateOptions({
              series: series
            });
          })
          .catch(error => console.error('Erro ao carregar dados dos arquivos:', error));
    })
    .catch(error => console.error('Erro ao obter o número de arquivos:', error));

// AREA CHART
const areaChartOptions = {
  series: [],
  chart: {
    type: 'area',
    background: 'transparent',
    height: 350,
    stacked: false,
    toolbar: {
      show: false,
    },
  },
  dataLabels: {
    enabled: false,
  },
  fill: {
    gradient: {
      opacityFrom: 0.4,
      opacityTo: 0.1,
      shadeIntensity: 1,
      stops: [0, 100],
      type: 'vertical',
    },
    type: 'gradient',
  },
  grid: {
    borderColor: '#55596e',
    yaxis: {
      lines: {
        show: true,
      },
    },
    xaxis: {
      lines: {
        show: true,
      },
    },
  },
  legend: {
    labels: {
      colors: '#f5f7ff',
    },
    show: true,
    position: 'top',
  },
  markers: {
    size: 6,
    strokeColors: '#1b2635',
    strokeWidth: 3,
  },
  stroke: {
    curve: 'smooth',
  },
  xaxis: {
    axisBorder: {
      color: '#55596e',
      show: true,
    },
    axisTicks: {
      color: '#55596e',
      show: true,
    },
    labels: {
      offsetY: 10,
      style: {
        colors: '#f5f7ff',
      },
    },
  },
  yaxis: [
    {
      title: {
        text: 'Sensor Valeur',
        style: {
          color: '#f5f7ff',
        },
      },
      labels: {
        style: {
          colors: ['#f5f7ff'],
        },
      },
    },
    {
      opposite: true,
      title: {
        text: 'Mesure',
        style: {
          color: '#f5f7ff',
        },
      },
      labels: {
        style: {
          colors: ['#f5f7ff'],
        },
      },
    },
  ],
  tooltip: {
    shared: true,
    intersect: false,
    theme: 'dark',
  },
};

const areaChart = new ApexCharts(
    document.querySelector('#area-chart'),
    areaChartOptions
);
areaChart.render();


// Obtém o modal
var modal = document.getElementById("myModal");

// Obtém o botão de fechar
var span = document.getElementsByClassName("close")[0];

// Quando o usuário clicar no botão, abra o modal
document.querySelector('.sidebar-list-item a').addEventListener('click', function(event) {
  event.preventDefault();
  modal.style.display = "block";
});

// Quando o usuário clicar no botão de fechar, feche o modal
span.onclick = function() {
  modal.style.display = "none";
}

// Quando o usuário clicar fora do modal, feche-o
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
function openModal() {
  const modal = document.getElementById('myModal');
  modal.style.display = 'block';
}










