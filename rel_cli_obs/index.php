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

<!DOCTYPE html>
<?php
if (isset($_SESSION['MM_Usuario'])) {
    echo '<html lang="pt-BR">'; // Fix versão antiga MK-AUTH
} else {
    echo '<html lang="pt-BR" class="has-navbar-fixed-top">';
}
?>
<html lang="pt-BR">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <title>MK - AUTH :: <?= htmlspecialchars($manifestTitle . " - V " . $manifestVersion); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="../../estilos/mk-auth.css">
    <link rel="stylesheet" href="../../estilos/font-awesome.css">
	<link href="../../estilos/bi-icons.css" rel="stylesheet" type="text/css" />
    <script src="../../scripts/jquery.js"></script>
    <script src="../../scripts/mk-auth.js"></script>
    <style>
        /* Estilos CSS personalizados */

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333;
        }

        form {
            background-color:#e4e4e4;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"],
        input[type="submit"],
        button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 2px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #0d6cea;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .client-count {
            color: #4caf50;
            font-weight: bold;
            margin-top: 20px;
        }

        .error-message {
            color: red;
            margin-top: 20px;
        }
		
	    /* Estilização dos ícones */
        .fas {
        margin-right: 5px; /* Adiciona um espaço entre o ícone e o texto */
        }

        /* Efeito de transição para o campo de busca */
        #search {
        width: 100%; 
        padding: 10px; 
        margin-bottom: 10px; 
        border: 1px solid #ccc; 
        transition: border-color 0.3s ease; /* Adiciona a transição */
        }

        /* Estilo da borda do campo de busca quando focado */
        #search:focus {
        border-color: #007bff; /* Cor da borda quando o campo está focado */
        outline: none; /* Remove a borda de foco padrão */
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

    <?php
    if ($acesso_permitido) {
        // Formulário Atualizado com Funcionalidade de Busca
    ?>
        <form id="searchForm" method="GET">
<div style="display: flex; justify-content: center; align-items: flex-end; margin-bottom: 10px;">
<div style="width: 60%; margin-right: 10px;">
    <form id="searchForm" method="GET" style="display: flex;">
        <label for="search" style="font-weight: bold; margin-bottom: 5px;">Buscar Cliente:</label>
        <input type="text" id="search" name="search" placeholder="Digite o Nome do Cliente" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" style="width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; cursor: text;"> <!-- Adicionando cursor: text; -->
    </form>
</div>
<div style="display: flex; align-items: flex-end;">
    <button type="submit" form="searchForm" id="searchButton" class="btn" style="padding: 10px 15px; border: 1px solid #4caf50; background-color: #4caf50; color: white; font-weight: bold; cursor: pointer; border-radius: 5px; margin-right: 10px;"><i class="fas fa-search"></i> Buscar</button>
    <button type="button" onclick="clearSearch()" class="clear-button" style="padding: 10px 15px; border: 1px solid #e74c3c; background-color: #e74c3c; color: white; font-weight: bold; cursor: pointer; border-radius: 5px; margin-right: 10px;"><i class="fas fa-times"></i> Limpar</button>
    <button type="button" onclick="sortTable(1)" class="clear-button sort-button-1" style="padding: 1.5px 15px; border: 1px solid #4336f4; background-color: #4336f4; color: white; font-weight: bold; cursor: pointer; border-radius: 5px;"><i class="fas fa-sort"></i> Ordenar</button>
</div>
</div>
        </form>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                    <th style="color: white;">Nome do Cliente</th>
                    <th style="color: white;">Data remover</th>
                    <th style="color: white;">Boletos Vencidos</th> <!-- Adicionando a coluna Boletos Vencidos -->
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Adicione a condição de busca, se houver
                    $searchCondition = '';
                    if (!empty($_GET['search'])) {
                        $search = mysqli_real_escape_string($link, $_GET['search']);
                        $searchCondition = " AND (c.login LIKE '%$search%' OR c.nome LIKE '%$search%')";
                    }

                    // Consulta SQL para obter os clientes em observação com data de remoção
                    $query = "SELECT c.uuid_cliente, c.nome, c.rem_obs, c.tit_vencidos
                              FROM sis_cliente c
                              WHERE c.cli_ativado = 's' AND c.observacao = 'sim'"
                        . $searchCondition .
                        " ORDER BY c.rem_obs DESC"; // Ordenar por data de remoção em ordem decrescente

                    // Execute a consulta
                    $result = mysqli_query($link, $query);

                    // Verifique se a consulta foi bem-sucedida
                    if ($result) {
                        // Inicialize a variável de contagem
                        $total_observacao_ = 0;
						
                        $rowNumber = 0;
                        while ($row = mysqli_fetch_assoc($result)) {
                            
							// Incrementar a contagem em cada iteração
                            $total_observacao_++;

                            // Adiciona a classe 'nome_cliente' e 'highlight' (para linhas ímpares) alternadamente
                            $rowNumber++;
                            $nomeClienteClass = ($rowNumber % 2 == 0) ? 'nome_cliente' : 'nome_cliente highlight';

                            // Adiciona o link apenas no campo de nome do cliente
                            echo "<tr class='$nomeClienteClass'>";
							
                            // Nome do Cliente
                            echo "<td style='position: relative; text-align: center; font-weight: bold;'>"; // Adicionando o estilo 'font-weight: bold;'
                            echo "<img src='img/icon_ativo.png' alt='Ícone de Nome' width='25' height='25' style='position: absolute; left: 0; top: 50%; transform: translateY(-50%);'> ";
                            echo "<a href='../../cliente_det.hhvm?uuid=" . $row['uuid_cliente'] . "' target='_blank'>" . $row['nome'] . "</a>";
                            echo "</td>";
							
                            // Data remover  
                            echo "<td style='border: 1px solid #ddd; padding: 1px; text-align: center; color: #e61515; font-weight: bold; position: relative; vertical-align: middle;'>"; // Adicionando o estilo 'vertical-align: middle;'
                            echo "<img src='img/calendario.png' alt='Ícone de Valor' width='20' height='20' style='position: absolute; left: 0; top: 50%; transform: translateY(-50%);'> ";
                            echo ($row['rem_obs'] ? date('d/m/Y', strtotime($row['rem_obs'])) : 'N/A');
                            echo "</td>";
                         
							// Titulos Vencidos
                            echo "<td style='border: 1px solid #ddd; padding: 1px; text-align: center; color: #e61515; font-weight: bold; position: relative;'>";
                            echo "<img src='img/icon_boleto.png' alt='Ícone de Valor' width='20' height='20' style='position: absolute; left: 0; top: 50%; transform: translateY(-50%);'> ";
                            echo $row['tit_vencidos'];
							echo "</tr>";
							echo "</td>";
                        }
                    } else {
                        // Se a consulta falhar, exiba uma mensagem de erro
                        echo "<tr><td colspan='3'>Erro na consulta: " . mysqli_error($link) . "</td></tr>";
                    }
                    ?>
                </tbody>
				
				<!--contador-->
				<div class="client-count-container" style="text-align: center;">
                <p class="client-count blue">Clientes em Observação: <?php echo $total_observacao_; ?></p>
                </div>

            </table>
        </div>
    <?php
    } else {
        echo "Acesso não permitido!";
    }
    ?>

    <?php include('../../baixo.php'); ?>

    <script src="../../menu.js.php"></script>
    <?php include('../../rodape.php'); ?>
</body>

</html>
