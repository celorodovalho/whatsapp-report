<?php

/**
 * Just parses the content of Whatsapp file to array format
 *
 * @param string $content
 * @return array
 * @throws Exception
 */
function getMsgsArray($content = '')
{
    preg_match_all('#(((\d{1,2}\/?){3}), \d{1,2}:\d{1,2} [APM]{2}) - (.*?): (.*)#', $content, $matches);
    $arrCount = [];
    if ($matches[4]) {
        foreach ($matches[4] as $key => $user) {
            if (!array_key_exists($user, $arrCount)) {
                $arrCount[$user] = ['msg' => 0, 'lastSent' => '', 'lastMsg' => ''];
            }
            $arrCount[$user]['msg']++;
            $arrCount[$user]['lastSent'] = DateTime::createFromFormat('m/d/y, h:i A', $matches[1][$key]);
            $arrCount[$user]['lastMsg'] = $matches[5][$key];

            $interval = $arrCount[$user]['lastSent']->diff(new DateTime());

            $arrCount[$user]['awayTime'] = $interval->days;
        }
    }
    array_multisort(array_column($arrCount, 'awayTime'), SORT_DESC, $arrCount);

    return $arrCount;
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Whatsapp Report</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <form method='post' enctype='multipart/form-data'>
        <div class="form-group">
            <label for="txtFile">Arquivo *.txt</label>
            <input type="file" class="form-control-file" id="txtFile" name="file">
            <small id="emailHelp" class="form-text text-muted">O arquivo exportado no Whatsapp.</small>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
    <?php
    if (!empty($_FILES) && $_FILES['file']['error'] === UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $arrCount = getMsgsArray(file_get_contents($_FILES['file']['tmp_name']));
        echo '<pre><code>';
        echo "Name\t\t\tMessages\t\t\tLast Activity\t\t\tDays Away\t\r";
        foreach ($arrCount as $name => $data) {
            echo sprintf(
                "@%s\t\t\t%s total\t\t\tLast msg: %s\t\t\t%s away\r",
                $name,
                $data['msg'],
                $data['lastSent']->format('d/m/Y'),
                $data['awayTime']
            );
        }
        echo '</code></pre>';
    }
    ?>
</div>
</body>
</html>