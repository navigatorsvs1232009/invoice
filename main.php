<?php
print_r($_REQUEST);
writeToLog($_REQUEST, 'incoming');

function writeToLog($data, $title = '') {
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/hook.log', $log, FILE_APPEND);
    return true;
}

function executeREST(string $method, array $params): array
{
    $queryUrl = 'https://crm.ocania.ru/rest/1/sssssssssssssssss/' . $method . '.json';
    $queryData = http_build_query($params);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ]);

    $result = curl_exec($curl);
    curl_close($curl);

    try {
        return json_decode($result, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return [];
    }
}

$deal_id = $_REQUEST['dealId'];
$totalSum = $_REQUEST['sum'];
$compId = $_REQUEST['companyId'];
$company = executeREST("crm.company.get", ["id" => $compId])['result'];
$kurs = $_REQUEST['kurs'];
$dealT = $_REQUEST['dealTitle'];
$codeOpl = $_REQUEST['paymentMethod'];
$contactID = $_REQUEST['contactId'];
$gallery_id = $_REQUEST['gallery'];


switch ($_GET['type']) {
    case 'Предоплата':
    {
        $type = 76;
        break;
    }
    case 'Доплата':
    {
        $type = 77;
        break;
    }
    case 'Оплата доставки':
    {
        $type = 78;
        break;
    }
    default:
        $type = 76;
}

switch ($_GET['gallery']) {
    case 'Галерея Б59':
    {
        $gallery = 6;
        $gal_ir = 30;
        break;
    }
    case 'Галерея П75':
    {
        $gallery = 7;
        $gal_ir = 31;
        break;
    }
    default:
        $gallery = 6;
        $gal_ir = 30;
}

switch ($_GET['paymentMethod']) {
    case 11:
    {
        $kodeOpl = 79;
        break;
    }
    case 12:
    {
        $kodeOpl = 80;
        break;
    }
    case 13:
    {
        $kodeOpl = 81;
        break;
    }
    case 14:
    {
        $kodeOpl = 82;
        break;
    }
    case 22:
    {
        $kodeOpl = 83;
        break;
    }
    default:
        $kodeOpl = 79;
}

$salon = $_REQUEST['salon'];

$productRow = executeREST('crm.deal.productrows.get', [
    'id' => $deal_id,
]);

$count = count($productRow['result']);



$rowproducts = [];



if ($_GET['isDealProducts'] == 'Да') {
    for ($i = 0; $i < $count; $i++) {
        $rowId = $productRow['result'][$i]['ID'];
        $rowName = $productRow['result'][$i]['PRODUCT_NAME'];
        $rowQ = $productRow['result'][$i]['QUANTITY'];
        $rowProdId = $productRow['result'][$i]['PRODUCT_ID'];
        $rowPrice = $productRow['result'][$i]['PRICE'];

        $rowproducts[] = [
            'ID' => $rowId,
            'PRODUCT_NAME' => $rowName,
            'QUANTITY' => $rowQ,
            'PRODUCT_ID' => $rowProdId,
            'PRICE' => $rowPrice,
        ];
    }
} else {
    $rowproducts[] =

                    [
                        'ID' => 0,
                        'PRODUCT_NAME' => '0',
                        'QUANTITY' => 1,
                        'PRICE' => $totalSum,
                    ];
}

    executeREST('crm.invoice.add', [
        'fields' => [
            'ORDER_TOPIC' => $dealT,
            'STATUS_ID' => 'S',
            'RESPONSIBLE_ID' => 1,
            'UF_COMPANY_ID' => $compId,
            'UF_CONTACT_ID' => $contactID,
            'PERSON_TYPE_ID' => 1,
            'PAY_SYSTEM_ID' => 1,
            'UF_DEAL_ID' => $deal_id,
            'UF_CRM_609E7F08E254D' => $gal_ir,
            'UF_MYCOMPANY_ID' => $gallery,
            'UF_CRM_1625494188' => $type,
            'UF_CRM_1625566195' => $kodeOpl,
            'UF_CRM_1632829122' => $kurs,
            'UF_CRM_60DB03EB18C2F' => $salon,
            'PAYED' => 'N',
            'PRODUCT_ROWS' => $rowproducts,
        ]
    ]);
//switch ($_GET['isDealProducts']) {
//    case 'Нет':
//    {
//        $type = 76;
//        break;
//    }
//    case 'Да':
//    {
//        $type = 77;
//        break;
//    }
//    default:
//        $type = 76;
//}

//for ($i = 0; $i < $count; $i++) {
//    $rowId = $productRow['result'][$i]['ID'];
//    $rowName = $productRow['result'][$i]['PRODUCT_NAME'];
//    $rowQ = $productRow['result'][$i]['QUANTITY'];
//    $rowProdId = $productRow['result'][$i]['PRODUCT_ID'];
//    $rowPrice = $productRow['result'][$i]['PRICE'];
//}


//$rowId = $productRow['result']['0']['ID'];
//$rowName = $productRow['result']['0']['PRODUCT_NAME'];
//$rowQ = $productRow['result']['0']['QUANTITY'];
//$rowProdId = $productRow['result']['0']['PRODUCT_ID'];
//$rowPice = $productRow['result']['0']['PRICE'];

//for ($i = 0; $i < $count; $i++) {
//    $rowId = $productRow['result'][$i]['ID'];
//    $rowName = $productRow['result'][$i]['PRODUCT_NAME'];
//    $rowQ = $productRow['result'][$i]['QUANTITY'];
//    $rowProdId = $productRow['result'][$i]['PRODUCT_ID'];
//    $rowPrice = $productRow['result'][$i]['PRICE'];
//}
//
//$rowproducts = [];


//            [
//            [
//                'ID' => $rowId,
//                'PRODUCT_NAME' => $rowName,
//                'QUANTITY' => $rowQ,
//                'PRODUCT_ID' => $rowProdId,
//                'PRICE' => $rowPrice,
//                'ID' => 5968,
//                'PRODUCT_NAME' => 'Кровать Cut LE05 L12 1581',
//                'QUANTITY' => 1,
//                'PRODUCT_ID' => 8314,
//                'PRICE' => 2032,

//            ]
//        ]
