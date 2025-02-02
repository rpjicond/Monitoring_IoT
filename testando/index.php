<!DOCTYPE html>
<?php //include 'conf/modules_info.php';
include 'conf/db/connexion.php';
// Redireciona para a mesma página após 5 segundos
//header("Refresh: 120; url=historique/historique.php");


//error_reporting(E_ALL);
//ini_set('display_errors', 1);


// Consulta SQL para recuperar os dados dos módulos
$sql = "SELECT Modules.name, historique.timestamp, historique.value
        FROM historique
        JOIN Modules ON historique.module_id = Modules.id";
if (!empty($connexion))
// Executar a consulta SQL usando o PDO
    try {
        $stmt = $connexion->query($sql);

        // Verificar se a consulta foi bem-sucedida
        if ($stmt) {
            // Loop através dos resultados da consulta
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $moduleName = $row['name'];
                $data = array(
                    'name' => $moduleName, // Adiciona o nome do módulo
                    'timestamp' => $row['timestamp'],
                    'value' => json_decode($row['value'], true) // Decodificar o valor JSON para um array associativo
                );

                // Construir o caminho do arquivo
                $filePath = "historique/modules_historique/$moduleName.json";

                // Verificar se o arquivo já existe
                if (file_exists($filePath)) {
                    // Ler o conteúdo atual do arquivo
                    $currentData = json_decode(file_get_contents($filePath), true);

                    // Adicionar o novo dado ao conteúdo atual
                    $currentData[] = $data;

                    // Codificar os dados atualizados de volta para JSON
                    $jsonData = json_encode($currentData, JSON_PRETTY_PRINT); // A opção JSON_PRETTY_PRINT organiza os dados verticalmente
                } else {
                    // Se o arquivo não existir, criar um novo array com o novo dado
                    $jsonData = json_encode(array($data), JSON_PRETTY_PRINT); // A opção JSON_PRETTY_PRINT organiza os dados verticalmente
                }

                // Escrever os dados JSON no arquivo
                file_put_contents($filePath, $jsonData);

                // Exibir uma mensagem de sucesso
                //echo "Dados do módulo '$moduleName' foram escritos no arquivo JSON '$filePath' com sucesso.<br>";
            }
        } else {
            // Se houver um erro na consulta, exibir uma mensagem de erro
            //echo "Erro ao executar a consulta SQL.";
        }
    } catch (PDOException $e) {
        // Se houver uma exceção, exibir uma mensagem de erro
        //echo "Erro ao executar a consulta SQL: " . $e->getMessage();
    }




// Redireciona para a mesma página após 5 segundos
//("Refresh: 1; url=./index.php");





// Chemin vers le fichier JSON stockant les informations des modules et le timestamp de la dernière exécution
$jsonFilePath = 'module_data.json';

// Définir l'intervalle de temps souhaité en secondes
$intervalo = 5; // Interval de 5 secondes pour le test (ajuster si nécessaire)

// Vérifier quand le script a été exécuté pour la dernière fois et récupérer les informations des modules
$jsonData = file_get_contents($jsonFilePath);
$data = json_decode($jsonData, true);

if(!empty($connexion))
// Vérifier si l'intervalle de temps a été atteint depuis la dernière exécution
    if (!$data || (time() - $data['timestamp']) > $intervalo) {
        // Si jamais exécuté ou si l'intervalle a été dépassé, exécuter le script
        try {
            // Récupérer les données des modules dans la base de données
            $query = $connexion->query("SELECT * FROM Modules");
            $modules = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($modules as $key => $module) {


                // Générer des valeurs aléatoires pour les capteurs en fonction du type de capteur associé
                $valeur = array();

                switch ($module['type']) {
                    case 'temperature':
                        $valeur['temperature'] = mt_rand(0, 50) / 10;
                        break;
                    case 'humidity':
                        $valeur['humidity'] = mt_rand(0, 60);
                        break;
                    case 'pressure':
                        $valeur['pressure'] = mt_rand(50, 70);
                        break;
                    case 'brightness':
                        $valeur['brightness'] = mt_rand(0, 1000);
                        break;
                    case 'smoke':
                        $valeur['smoke'] = mt_rand(0, 100);
                        break;
                    case 'motion':
                        $valeur['motion'] = mt_rand(0, 1);
                        break;
                    case 'obstacle':
                        $valeur['obstacle'] = mt_rand(0, 1);
                        break;
                    default:
                        // Type de capteur non reconnu
                        break;
                }

                // Générer l'état opérationnel de manière aléatoire
                $operationalStatus = (mt_rand(0, 1) == 1) ? 'operational' : 'non_opérationnel';

                // Générer une valeur aléatoire pour data_sent entre 0 et 1000
                $dataSent = mt_rand(0, 50);

                // Vérifier si l'enregistrement existe déjà dans la table Module_Data
                $checkQuery = $connexion->prepare("SELECT COUNT(*) as count FROM Module_Data WHERE module_id = :module_id");
                $checkQuery->bindParam(':module_id', $module['id']);
                $checkQuery->execute();
                $result = $checkQuery->fetch(PDO::FETCH_ASSOC);

                if ($result['count'] > 0) {

                    // Si l'enregistrement existe déjà, mettre à jour les valeurs dans la table Module_Data
                    $sql = "UPDATE Module_Data 
            SET value = :value, 
                operational_status = :operational_status,
                timestamp = NOW(),
                data_sent = :data_sent
            WHERE module_id = :module_id";

                    $stmt = $connexion->prepare($sql);
                    $valeurJSON = json_encode($valeur);
                    $stmt->bindParam(':value', $valeurJSON);
                    $stmt->bindParam(':operational_status', $operationalStatus);
                    $stmt->bindParam(':module_id', $module['id']);
                    $stmt->bindParam(':data_sent', $dataSent);
                    $stmt->execute();

                    // Insérer les données dans la table historique
                    $sqlInsert = "INSERT INTO historique (module_id, timestamp, value) VALUES (:module_id, NOW(), :value)";
                    $stmtInsert = $connexion->prepare($sqlInsert);
                    $stmtInsert->bindParam(':module_id', $module['id']);
                    $stmtInsert->bindParam(':value', $valeurJSON);
                    $stmtInsert->execute();


                    // Après l'insertion des données dans la table historique et la mise à jour des valeurs dans la table Module_Data,
                    // appeler la fonction pour mettre à jour le fichier JSON
                    updateAllModuleDataJson($connexion, 'all_module_data.json');
                }
                else {
                    // Si l'enregistrement n'existe pas, insérer un nouveau
                    $sql = "INSERT INTO Module_Data (module_id, value, operational_status, timestamp, data_sent) 
                        VALUES (:module_id, :value, :operational_status, NOW(), :data_sent)";

                    $stmt = $connexion->prepare($sql);
                    $valeurJSON = json_encode($valeur);
                    $stmt->bindParam(':module_id', $module['id']);
                    $stmt->bindParam(':value', $valeurJSON);
                    $stmt->bindParam(':operational_status', $operationalStatus);
                    $stmt->bindParam(':data_sent', $dataSent);
                    $stmt->execute();
                }

                // Ajouter les informations du module au tableau de données
                $modules[$key]['valeur'] = $valeur;
                $modules[$key]['operational_status'] = $operationalStatus;
            }

            // Ajouter le timestamp au tableau de données
            $modules['timestamp'] = time();

            // Mettre à jour le fichier JSON avec les informations des modules et le timestamp de la dernière exécution
            $jsonData = json_encode($modules, JSON_PRETTY_PRINT);
            file_put_contents($jsonFilePath, $jsonData);

            echo "Données insérées/mises à jour avec succès.";
        } catch (PDOException $e) {
            echo "Erreur: " . $e->getMessage();
        }
    } else {
        echo "L'intervalle n'a pas encore été atteint. Pas besoin de générer des données.";
    }

// Chemin du fichier JSON pour stocker toutes les données des modules
$jsonFilePathAll = 'all_module_data.json';

// Fonction pour mettre à jour le fichier JSON avec toutes les données des modules
function updateAllModuleDataJson($connexion, $jsonFilePath) {
    try {
        // Sélectionner toutes les données de la table Module_Data
        $queryAllData = $connexion->query("SELECT * FROM Module_Data");
        $allData = $queryAllData->fetchAll(PDO::FETCH_ASSOC);

        // Mettre à jour le fichier JSON avec toutes les données
        $jsonDataAll = json_encode($allData, JSON_PRETTY_PRINT);
        file_put_contents($jsonFilePath, $jsonDataAll);

        echo "Fichier JSON des données de tous les modules mis à jour avec succès.";
    } catch (PDOException $e) {
        echo "Erreur lors de la mise à jour du fichier JSON : " . $e->getMessage();
    }
}


// Inclure le fichier de connexion à la base de données
//include 'db/connexion.php';
//include 'modules.php';
//include './historique/historique.php';

// Tableau pour stocker les données
$data = array();
if(!empty($connexion))
// Requête pour obtenir le nombre total de modules
    $sql_total = "SELECT COUNT(*) AS total_modules FROM Modules";
$result_total = $connexion->query($sql_total);

// Vérifier si la requête a réussi
if ($result_total !== false) {
    // Extraire os dados da linha
    $ligne_total = $result_total->fetch(PDO::FETCH_ASSOC);
    $total_modules = $ligne_total["total_modules"];

    // Ajouter les données au tableau
    $data["total_modules"] = $total_modules;
}

// Requête pour obtenir le nombre de modules actifs
$sql_actifs = "SELECT COUNT(*) AS total_modules_actifs FROM Module_Data WHERE operational_status = 'operational'";
$result_actifs = $connexion->query($sql_actifs);

//Vérifier si la requête a réussi
if ($result_actifs !== false) {
    // Extraire les données de la ligne
    $ligne_actifs = $result_actifs->fetch(PDO::FETCH_ASSOC);
    $total_modules_actifs = $ligne_actifs["total_modules_actifs"];


    // Ajouter les données au tableau
    $data["total_modules_actifs"] = $total_modules_actifs;
}

// Requête pour obtenir le nombre de modules inactifs
$sql_inactifs = "SELECT COUNT(*) AS total_modules_inactifs FROM Module_Data WHERE operational_status != 'operational'";
$result_inactifs = $connexion->query($sql_inactifs);

// Vérifier si la requête a réussi
if ($result_inactifs !== false) {
    // Extraire les données de la ligne
    $ligne_inactifs = $result_inactifs->fetch(PDO::FETCH_ASSOC);
    $total_modules_inactifs = $ligne_inactifs["total_modules_inactifs"];

    // Ajouter les données au tableau
    $data["total_modules_inactifs"] = $total_modules_inactifs;
}

// Imprimir os dados obtidos das consultas SQL para depuração
//echo "Total de módulos: " . $total_modules . " ";
//echo "Total de módulos ativos: " . $total_modules_actifs . " ";
//echo "Total de módulos inativos: " . $total_modules_inactifs . " ";

// Chemin du fichier JSON
$chemin_fichier = "total_modules.json";

// Charger le contenu du fichier JSON actuel
$contenu_fichier = file_get_contents($chemin_fichier);

// Vérifier si les données ont changé ou s'il s'est écoulé 5 secondes depuis la dernière mise à jour
if ($contenu_fichier === false || time() - filemtime($chemin_fichier) >= 5) {
    // Convertir les nouvelles données en format JSON
    $json_data = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Écrire les nouvelles données dans le fichier JSON
    $ecriture_fichier = file_put_contents($chemin_fichier, $json_data);

}


// Vérifier si la connexion à la base de données est valide

// Requête pour obtenir la somme de toutes les données envoyées
$sql_total_data_sent = "SELECT SUM(data_sent) AS total_data_sent FROM Module_Data";
$result_total_data_sent = $connexion->query($sql_total_data_sent);

// Vérifier si la requête a réussi
if ($result_total_data_sent !== false) {
// Extraire les données de la ligne
    $row_total_data_sent = $result_total_data_sent->fetch(PDO::FETCH_ASSOC);
    $total_donnees_envoyees = $row_total_data_sent["total_data_sent"];

// Vérifier si le total est null (aucune donnée envoyée)
    if ($total_donnees_envoyees === null) {
        $total_donnees_envoyees = 0; // Définir le total à 0 s'il n'y a aucune donnée envoyée
    }
}


// Retourner les données
//echo json_encode([
//'total_modules_actifs' => $total_modules_actifs,
//'total_modules_inactifs' => $total_modules_inactifs
//]);




?>

<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta http-equiv="refresh" content="">
    <title>Suivi de l'IoT</title>

    <!-- Police Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Icônes Material -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">

    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div class="grid-container">

    <!-- En-tête -->
    <header class="header">
        <div class="menu-icon" onclick="openSidebar()">
            <span class="material-icons-outlined">menu</span>
        </div>

        <div class="header-right">
            <span class="material-icons-outlined">notifications</span>

        </div>
    </header>
    <!-- Fin de l'en-tête -->

    <!-- Barre latérale -->
    <aside id="sidebar">
        <div class="sidebar-title">
            <div class="sidebar-brand">
                <span class="material-icons-outlined">dashboard</span> SURVEILLANCE
            </div>
            <span class="material-icons-outlined" onclick="closeSidebar()">close</span>
        </div>


        <ul class="sidebar-list">
            <li class="sidebar-list-item">
                <a href="#" target="_blank" >
                    <span class="material-icons-outlined">dashboard</span> Menu
                </a>
            </li>
            <li class="sidebar-list-item">
                <a  target="_blank" onclick="openModal()">
                    <span class="material-icons-outlined">settings</span> Modules
                </a>
            </li>

        </ul>
    </aside>
    <!-- Fin de la barre latérale -->

    <!-- Principal -->
    <main class="main-container">
        <div class="main-title">
            <h2>Monitoring DE MODULES IOT</h2>
        </div>

        <div class="main-cards">

            <div class="card">
                <div class="card-inner">
                    <h3>MODULES TOTAUX</h3>
                    <span class="material-icons-outlined">devices_other</span>
                </div>
                <h1><?php if(!empty($total_modules)) echo $total_modules; ?></h1>
            </div>

            <div class="card">
                <div class="card-inner">
                    <h3>DONNÉES ENVOYÉES</h3>
                    <span class="material-icons-outlined">data_usage</span>
                </div>

                <h1><?php if(!empty($total_donnees_envoyees)) {echo $total_donnees_envoyees;} ?></h1>
            </div>
            <div class="card">
                <div class="card-inner">
                    <h3>MODULES ACTIFS</h3>
                    <span class="material-icons-outlined">power</span>
                </div>
                <h1 id="modules-actifs"><?php if (!empty($total_modules_actifs)) echo $total_modules_actifs; ?></h1>
            </div>

            <div class="card">
                <div class="card-inner">
                    <h3>MODULES INACTIFS</h3>
                    <span class="material-icons-outlined">power_off</span>
                </div>
                <h1 id="modules-inactifs"><?php if(!empty($total_modules_inactifs)) echo $total_modules_inactifs; ?></h1>
            </div>

        </div>

        <div id="charts"  class="charts">

            <div  class="charts-card">

                <h2 class="chart-title">ALERTE</h2>
                <div id="bar-chart"></div>
            </div>

            <div class="charts-card">
                <h2 class="chart-title">Graphique de Surveillance IoT</h2>
                <div id="area-chart"></div>
            </div>
        </div>

        <!-- Tableau -->
        <div class="charts-table">
            <table>
                <thead>
                <tr>
                    <th><span class="material-icons-outlined">info</span> ID</th>
                    <th>Nom <span class="material-icons-outlined icon-description">description</span></th>
                    <th>Description <span class="material-icons-outlined icon-subject">subject</span></th>
                    <th>Valeur Mesurée <span class="material-icons-outlined icon-timeline">timeline</span></th>
                    <th>Temps de fonctionnement <span class="material-icons-outlined icon-schedule">schedule</span></th>
                    <th>Données envoyées <span class="material-icons-outlined icon-send">send</span></th>
                    <th>État Opérationnel <span class="material-icons-outlined icon-check-circle">check_circle_outline</span></th>
                </tr>
                </thead>
                <tbody>

                <!-- Ajouter plus de lignes au besoin -->
                <?php


               if(!empty($connexion))
                // Requête SQL pour récupérer les données des modules depuis la base de données
                $query = $connexion->query("SELECT md.id, m.name, m.description, md.value, md.timestamp, md.data_sent, md.operational_status, m.type 
                            FROM Module_Data md
                            JOIN Modules m ON md.module_id = m.id");
                $modules = $query->fetchAll(PDO::FETCH_ASSOC);

                // Fonction pour obtenir l'unité en fonction du type de capteur
                function getUnit($type) {
                    switch ($type) {
                        case 'temperature':
                            return '°C'; // Celsius
                        case 'humidity':
                            return '%'; // Pourcentage
                        case 'pressure':
                            return 'Pa'; // Pascal
                        case 'brightness':
                            return 'Lux'; // Lux
                        // Ajouter d'autres cas au besoin
                        default:
                            return ''; // Unité inconnue
                    }
                }

                // Parcourir les données et remplir le tableau
                foreach ($modules as $module) {
                    echo "<tr>";
                    echo "<td>{$module['id']}</td>";
                    echo "<td>{$module['name']}</td>";
                    echo "<td>{$module['description']}</td>";

                    // Extraire la valeur du JSON et afficher uniquement le nombre avec son unité
                    $value = json_decode($module['value'], true);
                    $sensorType = $module['type'];
                    $sensorValue = isset($value[$sensorType]) ? $value[$sensorType] : '';
                    $unit = getUnit($sensorType);
                    echo "<td>{$sensorValue} {$unit}</td>";

                    echo "<td>{$module['timestamp']}</td>";
                    echo "<td>{$module['data_sent']}</td>"; // Assurez-vous que la colonne 'data_sent' existe dans votre base de données
                    echo "<td class='" . ($module['operational_status'] === "operational" ? "active-status" : "inactive-status") . "'>";
                    if ($module['operational_status'] === "operational") {
                        echo "actif"; // Affiche la valeur de $module['operational_status']
                    }else {
                        echo "inactif";
                    }




                    echo "</td>";
                    echo "</tr>";
                }
                ?>




                </tbody>
            </table>
        </div>
        <!-- Fin du tableau -->

        <!-- Modal -->
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2 class="modal-title">Enregistrer un Nouveau Module IoT</h2>
                <form id="moduleForm" method="post" action="conf/enregistrement_modulateurs.php">
                    <label for="moduleName">Nom du Module</label>
                    <input type="text" id="moduleName" name="moduleName" required>

                    <label for="moduleDescription">Description</label>
                    <textarea id="moduleDescription" name="moduleDescription" required></textarea>

                    <label for="moduleType">Type de Module</label>
                    <select id="moduleType" name="moduleType" required>
                        <option value="" disabled selected>Choisir le type de module...</option>
                        <option value="temperature">Capteur de Température</option>
                        <option value="pressure">Capteur de Pression</option>
                        <option value="humidity">Capteur d'Humidité</option>
                        <!-- Ajouter plus d'options au besoin -->
                    </select>

                    <button type="submit">Enregistrer</button>
                </form>
            </div>
        </div>
        <!-- Modal info-->
        <!-- Modal -->
        <div id="myModal" class="modal" style="display: <?php echo $displayModal ? 'block' : 'none'; ?>">
            <div class="modal-content">
                <span class="close">&times;</span>
                <p><?php echo $message; ?></p>
            </div>
        </div>




    </main>
    <!-- Fin du principal -->


</div>

<!-- Scripts -->
<!-- ApexCharts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/apexcharts/3.35.5/apexcharts.min.js"></script>
<!-- JS personnalisé -->
<script src="js/scripts.js"></script>
<script src="js/modal.js"></script>
<!-- Script JavaScript para atualização -->


<script>
    // Usando múltiplas variáveis PHP em JavaScript
    var valor1 = "<?php echo $total_modules; ?>";
    var valor2 = "<?php echo $total_modules_inactifs; ?>";
    var valor3 = "<?php echo $total_modules_actifs; ?>";
    var valor4 = "<?php echo $total_donnees_envoyees; ?>";

    // ---------- CHARTS ----------


    // BAR CHART
    const barChartOptions = {
        series: [
            {
                data: [valor1, valor2, valor3,valor4], // Dados de exemplo
                name: 'M',
            },
        ],
        chart: {
            type: 'bar',
            background: 'transparent',
            height: 350,
            toolbar: {
                show: false,
            },
        },
        colors: ['#2962ff', '#d50000', '#2e7d32', '#ff6d00', '#583cb3'],
        plotOptions: {
            bar: {
                distributed: true,
                borderRadius: 4,
                horizontal: false,
                columnWidth: '40%',
            },
        },
        dataLabels: {
            enabled: false,
        },
        fill: {
            opacity: 1,
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
        stroke: {
            colors: ['transparent'],
            show: true,
            width: 2,
        },
        tooltip: {
            shared: true,
            intersect: false,
            theme: 'dark',
        },
        xaxis: {
            categories: ['Total Modules', 'Inactive Modules', 'Active Modules', 'Total Data'],
            title: {
                style: {
                    color: '#f5f7ff',
                },
            },
            axisBorder: {
                show: true,
                color: '#55596e',
            },
            axisTicks: {
                show: true,
                color: '#55596e',
            },
            labels: {
                style: {
                    colors: '#f5f7ff',
                },
            },
        },
        yaxis: {
            title: {
                text: 'Count',
                style: {
                    color: '#f5f7ff',
                },
            },
            axisBorder: {
                color: '#55596e',
                show: true,
            },
            axisTicks: {
                color: '#55596e',
                show: true,
            },
            labels: {
                style: {
                    colors: '#f5f7ff',
                },
            },
        },
    };

    // Criar o gráfico de barras
    const barChart = new ApexCharts(
        document.querySelector('#bar-chart'),
        barChartOptions
    );
    barChart.render();



</script>




</body>
</html>





