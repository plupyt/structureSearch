<?php
function connectDB()
{
    $connection = mysqli_connect("127.0.0.1", "root", "", "indexing");
    if (!$connection) {
        die("Fail: " . mysqli_connect_error());
    }
    return $connection;
}

function closeConnectionBD($connection)
{
    mysqli_close($connection);
}

function executeQuery($query)
{
    $connection = connectDB();
    $queryResults = mysqli_query($connection, $query);

    if ($queryResults) {
        $arrayResults = readQueryResults($queryResults);
    } else {
        $arrayResults = array();
    }

    closeConnectionBD($connection);
    return $arrayResults;
}

function readQueryResults($results)
{
    $arrayResults = array();
    while ($fila = mysqli_fetch_array($results)) {

        $arrayResults[] = $fila;
    }
    return $arrayResults;
}

function updateDB($query)
{
    $connection = connectDB();
    $done = mysqli_query($connection, $query);
    closeConnectionBD($connection);
    return $done;
}

function insertFile($ID, $content)
{
    $query = "INSERT INTO posting (Document_ID,Document_Content) VALUES(" . $ID . ",'" . $content . "');";
    return updateDB($query);
}

function insertKeyword($keyword)
{
    $query = "INSERT INTO dictionary (Keyword,Keyword_Appearances) VALUES ('" . $keyword . "',1);";
    return updateDB($query);
}

function getDocContent($documentID)
{
    $query = "SELECT posting.Document_Content FROM posting WHERE Document_ID = " . $documentID . ";";
    $content = executeQuery($query);
    if (!empty($content)) {
        return $content[0]["Document_Content"];
    } else {
        return "404 NOT FOUND :(";
    }
}

function keywordExists($keyword)
{
    $checkQuery = "SELECT dictionary.Keyword FROM dictionary WHERE KeyWord = '" . $keyword . "';";

    if (empty(executeQuery($checkQuery))) {
        return false;
    } else {
        return true;
    }
}

function increaseAppearances($keyword)
{
    $query = "UPDATE dictionary SET Keyword_Appearances = Keyword_Appearances + 1 WHERE Keyword = '" . $keyword . "';";
    return updateDB($query);
}


function uploadKeyword($keyword)
{
    if (keywordExists($keyword)) {
        increaseAppearances($keyword);
    } else {
        insertKeyword($keyword);
    }
}

function newKeyword($keyword, $documentID, $frequency, $positions)
{
    $query = "INSERT INTO keyword_post (Keyword,Document_ID,Frequency,Positions) VALUES ('" .
        $keyword . "','" .
        $documentID . "','" .
        $frequency . "','" .
        $positions .
        "');";
    var_dump($query);
    $done = updateDB($query);

    if ($done) {
        uploadKeyword($keyword);
    }

    return $done;
}

function createDocumentTxt($id_document){
    $fileName = "document_$id_document.txt";
    $dataFile = getDocContent($id_document);
    if ($dataFile ==  "404 NOT FOUND :(") {
        echo "ERROR - ARCHIVO NO ENCONTRADO";
    } else {
        file_put_contents("documents/" . $fileName, $dataFile);
    }
}

function getFilesDocuments($id_document)
{
    $files = scandir("documents/");
    if (in_array("document_$id_document.txt", $files)) {
    } else {
       createDocumentTxt($id_document);
    }
    return $files;
}

function downloadDocument($id_document)
{
    getFilesDocuments($id_document); //Lo llamo antes en caso de que no me haya creado el archivo para descargarlo.
    $files = getFilesDocuments($id_document);

    for ($i = 2; $i < count($files); $i++) {
        if ($files[$i] == "document_$id_document.txt") {
?>
            <a download="<?php echo $files[$i] ?>" href="documents/<?php echo $files[$i] ?>"><?php echo $files[$i] ?></a>
<?php
        }
    }
}
