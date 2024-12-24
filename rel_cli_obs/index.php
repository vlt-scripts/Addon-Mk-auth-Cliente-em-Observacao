<?php
// INCLUE FUNCOES DE ADDONS -----------------------------------------------------------------------
include('addons.class.php');

// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------
session_name('mka');
if (!isset($_SESSION)) session_start();
if (!isset($_SESSION['mka_logado']) && !isset($_SESSION['MKA_Logado'])) exit('Acesso negado... <a href="/admin/login.php">Fazer Login</a>');
// VERIFICA SE O USUARIO ESTA LOGADO --------------------------------------------------------------

// Assuming $Manifest is defined somewhere before this code
$manifestTitle = $Manifest->{'name'} ?? '';
$manifestVersion = $Manifest->{'version'} ?? '';
?>

<?php
// Include necessary files
require_once('config.php');

// Search and filter logic
$searchCondition = '';
$searchTerm = '';
if (!empty($_GET['search'])) {
    $searchTerm = mysqli_real_escape_string($link, $_GET['search']);
    $searchCondition = " AND (c.login LIKE '%$searchTerm%' OR c.nome LIKE '%$searchTerm%')";
}

// Fetch observation clients
$query = "SELECT c.uuid_cliente, c.nome, c.rem_obs, c.tit_vencidos
          FROM sis_cliente c
          WHERE c.cli_ativado = 's' AND c.observacao = 'sim'"
    . $searchCondition .
    " ORDER BY c.rem_obs DESC";

$result = mysqli_query($link, $query);
$observationClients = [];
$totalObservationClients = 0;

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $observationClients[] = $row;
        $totalObservationClients++;
    }
}
?>


<!DOCTYPE html>
<html lang="pt-BR" class="has-navbar-fixed-top" style="margin-top: 20px;">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <title>MK - AUTH :: <?= htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?></title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
	
	<!-- Bootstrap 5 CSS -->
    <link href="bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    
	
    <link rel="stylesheet" href="../../estilos/font-awesome.css">
	<link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>
	<link rel="stylesheet" href="../../estilos/mk-auth.css">
    <style>
        body {
            background-color: #ffffff;
        }
        .table-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-top: 20px;
        }
        .table > thead {
            background-color: #0d6cea;
            color: white;
        }
        .table > tbody > tr:hover {
            background-color: #f1f3f5;
            transition: background-color 0.3s ease;
        }
        .client-badge {
            font-size: 1rem;
            font-weight: bold;
        }
        .search-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
		/* Estilo inicial do link */
        .client-link {
        color: #063572; /* Azul padrão */
        font-weight: bold; /* Negrito */
        text-decoration: none; /* Remove sublinhado */
        transition: color 0.3s ease; /* Transição suave para cor */
        }   

        /* Estilo ao passar o mouse */
       .client-link:hover {
        color: #df830a; /* Azul mais escuro no hover */
        text-decoration: underline; /* Adiciona sublinhado no hover */
        }
		h4.mb-0 {
        font-weight: bold;
        }
    </style>

<script type="text/javascript">
    function clearSearch() {
        document.getElementById('search').value = '';
        document.forms['searchForm'].submit();
    }

    document.addEventListener("DOMContentLoaded", function() {
        var cells = document.querySelectorAll('.table-container tbody td.plan-name');
        cells.forEach(function(cell) {
            cell.addEventListener('click', function() {
                var planName = this.innerText;
                document.getElementById('search').value = planName;
                document.title = 'Painel: ' + planName;
                document.forms['searchForm'].submit();
            });
        });
    });

function compareDates(date1, date2) {
    var parts1 = date1.split('/');
    var parts2 = date2.split('/');
    var day1 = parseInt(parts1[0], 10);
    var month1 = parseInt(parts1[1], 10);
    var year1 = parseInt(parts1[2], 10);
    var day2 = parseInt(parts2[0], 10);
    var month2 = parseInt(parts2[1], 10);
    var year2 = parseInt(parts2[2], 10);

    if (year1 !== year2) {
        return year2 - year1; // Ordenar por ano
    } else if (month1 !== month2) {
        return month2 - month1; // Ordenar por mês
    } else {
        return day2 - day1; // Ordenar por dia
    }
}

function sortTable(columnIndex) {
    var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
    table = document.querySelector('.table-container table');
    switching = true;
    dir = 'asc';
    while (switching) {
        switching = false;
        rows = table.rows;
        for (i = 1; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            x = rows[i].getElementsByTagName("TD")[columnIndex];
            y = rows[i + 1].getElementsByTagName("TD")[columnIndex];
            if (dir == "asc") {
                if (getDateFromTableCell(y.innerHTML) < getDateFromTableCell(x.innerHTML)) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir == "desc") {
                if (getDateFromTableCell(y.innerHTML) > getDateFromTableCell(x.innerHTML)) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

function getDateFromTableCell(cellContent) {
    // Extrair a data e o ano do conteúdo da célula
    var datePattern = /(\d{2}\/\d{2}\/\d{4})/; // Padrão de data DD/MM/AAAA
    var match = cellContent.match(datePattern);
    if (match) {
        var dateString = match[0];
        var parts = dateString.split('/');
        var year = parseInt(parts[2]);
        var month = parseInt(parts[1]) - 1; // Mês começa de 0 (janeiro é 0)
        var day = parseInt(parts[0]);
        return new Date(year, month, day);
    }
    return null; // Retorna nulo se não encontrar uma data válida
}


</script>

</head>

<body>
    <?php include('../../topo.php'); ?>

    <nav class="breadcrumb has-bullet-separator is-centered" aria-label="breadcrumbs">
        <ul>
            <li><a href="#"> ADDON</a></li>
            <li class="is-active">
                <a href="#" aria-current="page"> <?php echo htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?> </a>
            </li>
        </ul>
    </nav>

    <?php include('config.php'); ?>

        <?php if ($acesso_permitido): ?>
<!-- Search Container -->
<div class="search-container">
    <form id="searchForm" method="GET" class="row g-3">
        <div class="col-md-6">
            <label for="search" class="form-label">Buscar Cliente</label>
            <div class="input-group">
                <!-- Input de busca -->
                <input type="text" 
                       class="form-control" 
                       id="search" 
                       name="search" 
                       placeholder="Digite o Nome do Cliente" 
                       value="<?= htmlspecialchars($searchTerm); ?>">
                
                <!-- Botão de buscar -->
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i> Buscar
                </button>
                
                <!-- Botão de limpar -->
                <button class="btn btn-danger" type="button" onclick="clearSearch()">
                    <i class="fas fa-times"></i> Limpar
                </button>
                
                <!-- Botão de ordenar -->
                <button type="button" 
                        onclick="sortTable(1)" 
                        class="btn btn-secondary" 
                        style="padding: 0.375rem 0.75rem; font-weight: bold;">
                    <i class="fas fa-sort"></i> Ordenar
                </button>
            </div>
        </div>
    </form>
</div>


            <!-- Clients Table Container -->
<div class="table-container">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
        <h4 class="mb-0 fw-bold text-primary">Clientes em Observação</h4>
        <span class="badge bg-secondary text-white fs-6 px-3 py-2">
            Total: <?= $totalObservationClients; ?>
        </span>
    </div>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Nome do Cliente</th>
                            <th>Data para Remover</th>
                            <th>Boletos Vencidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($observationClients)): ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Nenhum cliente em observação encontrado.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($observationClients as $client): ?>
                                <tr>
<td>
    <a href="../../cliente_det.hhvm?uuid=<?= $client['uuid_cliente']; ?>" 
       target="_blank" 
       class="client-link">
        <i class="fas fa-user me-2"></i>
        <?= htmlspecialchars($client['nome']); ?>
    </a>
</td>
<td style="color: #2f4f4f; font-weight: bold;">
    <i class="fas fa-calendar me-2"></i>
    <?= $client['rem_obs'] ? date('d/m/Y', strtotime($client['rem_obs'])) : 'N/A'; ?>
</td>

<td style="color: #e63946; font-weight: bold;">
    <i class="fas fa-file-invoice-dollar me-2"></i>
    <?= htmlspecialchars($client['tit_vencidos']); ?>
</td>

                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-danger" role="alert">
                Acesso não permitido!
            </div>
        <?php endif; ?>
    </div>
    <?php include('../../baixo.php'); ?>

    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>
</body>

</html>
