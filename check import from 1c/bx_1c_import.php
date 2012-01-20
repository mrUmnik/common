<?
//created by C_arter /*hello from bitrix's support*/
//last modified: 12.09.2010
/////////////////////////preparing =)///////////////////////////////////////////

global $DBDebugToFile;
$DBDebugToFile = true;

define('ver', '2.5');
define('MENULINES', 5);
$APicture=Array('jpg','jpeg','gif','png');
$UPLOAD_DIR='/upload';
$script_name=$_SERVER['SCRIPT_NAME'];
$catalog_import_path='/bitrix/admin/1c_exchange.php';
$user_import_path='/bitrix/admin/1c_intranet.php';
$is_bitrix_dir=strpos($_SERVER['DOCUMENT_ROOT'].$script_name,$_SERVER['DOCUMENT_ROOT'].'/bitrix');
if ((@$_REQUEST['mode']!='query' && @$_REQUEST['mode']!='exchange'))
define('NEED_AUTH',true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");



if (@$_REQUEST['mode']=='exchange')
{
	$data=CUtil::JsObjectToPhp($_REQUEST['data']);
	$req=new CHTTP;
	$req->SetAuthBasic($data['login'],$data['pass']);	
	$URL=$data['url'];

	if (!$data['phpsessid'])
		$URL.='?mode=checkauth&type=catalog';
	else
	{
		$URL.='?mode=import&type=catalog&filename='.$data['filename'];
		$req->additional_headers['Cookie'] = 'PHPSESSID='.$data['phpsessid'].';';
	}
	$arUrl=$req->ParseURL($URL);

	$req->Query('GET',$arUrl['host'],$arUrl['port'],$arUrl['path_query']);

	$body=explode("\n",$req->result);
	if (count($body)>1)
	{

		$response['status']=$body[0];

		if ($response['status']=='success' && $body[1]=='PHPSESSID')
				$response['phpsessid']=$body[2];
		else
		{	
			if (ToUpper($req->headers['Content-Type'])!=ToUpper('text/html; charset=utf-8'))
			$body=$APPLICATION->ConvertCharsetArray($body,'windows-1251','UTF-8');	
			$response['text']=$body[1];
		}
			
	}
	else
		$response['error']=$body;
	foreach($req->headers as $key=>$value)
		$response['headers'].="<b>".$key."</b>: ".$value."<br/>";
			
	echo json_encode($response);
	die();
}

if (@$_REQUEST['type']=='catalog')
{
    AddEventHandler("iblock", "OnAfterIBlockElementAdd",  "WriteElementAddDebug");
    AddEventHandler("iblock", "OnAfterIBlockElementUpdate",  "WriteElementUpdateDebug");
	
	function WriteElementAddDebug(&$arFields)
	{
		define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/import_element_log.txt");
		AddMessage2Log(print_r($arFields,true), "------------ADD-----------");
	}
	
	function WriteElementUpdateDebug(&$arFields)
	{
		define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/import_element_log.txt");
		AddMessage2Log(print_r($arFields,true), "------------UPDATE-----------");
	}
	

	$APPLICATION->IncludeComponent("bitrix:catalog.import.1c", "", Array(
		"IBLOCK_TYPE" => COption::GetOptionString("catalog", "1C_IBLOCK_TYPE", "-"),
		"SITE_LIST" => array(COption::GetOptionString("catalog", "1C_SITE_LIST", "-")),
		"INTERVAL" => COption::GetOptionString("catalog", "1C_INTERVAL", "-"),
		"GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("catalog", "1C_GROUP_PERMISSIONS", "")),
		"GENERATE_PREVIEW" => COption::GetOptionString("catalog", "1C_GENERATE_PREVIEW", "Y"),
		"PREVIEW_WIDTH" => COption::GetOptionString("catalog", "1C_PREVIEW_WIDTH", "100"),
		"PREVIEW_HEIGHT" => COption::GetOptionString("catalog", "1C_PREVIEW_HEIGHT", "100"),
		"DETAIL_RESIZE" => COption::GetOptionString("catalog", "1C_DETAIL_RESIZE", "Y"),
		"DETAIL_WIDTH" => COption::GetOptionString("catalog", "1C_DETAIL_WIDTH", "300"),
		"DETAIL_HEIGHT" => COption::GetOptionString("catalog", "1C_DETAIL_HEIGHT", "300"),
		"ELEMENT_ACTION" => COption::GetOptionString("catalog", "1C_ELEMENT_ACTION", "D"),
		"SECTION_ACTION" => COption::GetOptionString("catalog", "1C_SECTION_ACTION", "D"),
		"FILE_SIZE_LIMIT" => COption::GetOptionString("catalog", "1C_FILE_SIZE_LIMIT", 200*1024),
		"USE_CRC" => COption::GetOptionString("catalog", "1C_USE_CRC", "Y"),
		"USE_ZIP" => COption::GetOptionString("catalog", "1C_USE_ZIP", "Y"),
		"USE_OFFERS" => COption::GetOptionString("catalog", "1C_USE_OFFERS", "N"),
		"USE_IBLOCK_TYPE_ID" => COption::GetOptionString("catalog", "1C_USE_IBLOCK_TYPE_ID", "N"),
		"USE_IBLOCK_PICTURE_SETTINGS" => COption::GetOptionString("catalog", "1C_USE_IBLOCK_PICTURE_SETTINGS", "N"),
		"TRANSLIT_ON_ADD" => COption::GetOptionString("catalog", "1C_TRANSLIT_ON_ADD", "N"),
		"TRANSLIT_ON_UPDATE" => COption::GetOptionString("catalog", "1C_TRANSLIT_ON_UPDATE", "N"),
		)
	);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

	die();
	
}


if (!isset($_REQUEST['action']))
{
    $arUrlCreateIBlockType = array(
    "URL"=> $script_name.'?action=createiblocktypeform',
    "PARAMS"=> array(
    "width" => '300',
    "height" => '200',
    "title"=>'Создать тип инфоблока',
    "resizable" => false
    )
    );

    $arUrlChangeTime = array(
    "URL"=> $script_name.'?action=change_time',
    "head"=>'123123',
    "PARAMS"=> array(
    "width" => '300',
    "height" => '120',
    "title"=>'12313',
    "resizable" => false,

    )
    );


    //$CreateIBLOCK=$APPLICATION->GetPopupLink($arUrlCreateIBlockType);
    $change_time=$APPLICATION->GetPopupLink($arUrlChangeTime);
}
//Готовим кнопки
$MenuArray=Array(
            'main_info'=>Array(
                "msg"=>'откроется окно информации по файлам',
                "title"=>'Поиск',
                "onclick"=>"BX('main_info').style.display='block'",
                "class"=>'small_but'
                ),
            'main'=>Array(
                    "msg"=>'откроется окно сброса даты последнего обмена с 1С',
                    "title"=>'Метка времени',
                    "onclick"=>"javascript:".$change_time,
                    "class"=>'small_but'
            ),
            'param'=>Array(
                    "msg"=>'откроется окно параметров выгрузки заказов',
                    "title"=>'Выгрузка заказов',
                    "onclick"=>"BX('param').style.display='block'",
                    "class"=>'small_but'
            ),
            'stepdiag'=>Array(
                        "msg"=>'появится окно пошагового импорта. Пошаговая диагностика только для файлов каталога из папки '.$UPLOAD_DIR.'/1c_catalog/',
                        "title"=>'Пошаговый импорт',
                        "onclick"=>"BX('stepdiag').style.display='block'",
                        "class"=>'small_but'
                        ),

            'list'=>Array(
                    "msg"=>'откроется список заказов, которые выгрузятся в 1С. Сначала его нужно сформировать в окне параметров выгрузки заказов.',
                    "title"=>'Заказы (инф.)',
                    "onclick"=>"BX('list').style.display='block'",
                    "class"=>'small_but'
                    ),
            'crtiblock'=>Array(
                        "msg"=>'откроется окно создания типа инфоблока',
                        "title"=>'Создать тип инфоблока',
                        "onclick"=>"AddWindowRequest('".$script_name."?action=createiblocktypeform','custom_windows','iblock');",
                        "class"=>'small_but'
                        ),

            'xmltree'=>Array(
                                    "msg"=>'будет отображено содержимое временной таблицы',
                                    "title"=>'Временная таблица',
                                    "onclick"=>"AjaxRequest('".$script_name."?action=show_bxmltree','tab1_field',false)",
                                    "class"=>'small_but'
                                    ),
         
                    );

//  $CustomButton - массив кастомных кнопок
$CustomButton['searchbutton']=Array(
							"msg"=>'произойдёт поиск',
							"title"=>'найти',
							"onclick"=>"searchbyxmlid();",
							"class"=>'small_but'
							);
$CustomButton['change1']=Array(
							"msg"=>'сменится время последнего обмена с 1С, после этого посмотреть список заказов, которые выгрузятся в 1С при следующем обмене',
							"title"=>'Сменить',
							"onclick"=>"ChangeLastMoment();",
							"class"=>'small_but'
							);
$CustomButton['delete']=Array(
							"msg"=>'удалится весь этот скрипт',
							"title"=>'Удалить скрипт',
							"onclick"=>"delete_file()",
							"class"=>'small_but'
							);		
$CustomButton['refresh']=Array(
							"msg"=>'обнулится шаг импорта',
							"title"=>'Обнулить шаг',
							"onclick"=>"reset()",
							"class"=>'small_but'
							);	
$CustomButton['cat_imp']=Array(
							"msg"=>"Импорт файла, это импорт каталога",
							"title"=>'Каталог',
							"onclick"=>"ConfirmImport('import.xml');",
							"class"=>'small_but'
							);
$CustomButton['cat_off']=Array(
							"msg"=>"Импорт файла, это импорт предложений",
							"title"=>'Предложения',
							"onclick"=>"ConfirmImport('offers.xml');",
							"class"=>'small_but'
							);
$CustomButton['order_import']=Array(
							"msg"=>"Импорт файла, это импорт заказов",
							"title"=>'Импорт заказов',
							"onclick"=>"OrderImport('ord_imp');",
							"class"=>'small_but'
							);
$CustomButton['cat_comp']=Array(
							"msg"=>"Импорт файла, это импорт сотрудников",
							"title"=>'Сотрудники',
							"onclick"=>"ConfirmImport('company.xml');",
							"class"=>'small_but'
							);
$CustomButton['iblockbut']=Array(
							"msg"=>'создастся тип инфоблока',
							"title"=>'создать',
							"onclick"=>"CreateIBlock();",
							"class"=>'small_but'
							);
$CustomButton['test_123']=Array(
							"msg"=>'откроется FileMan',
							"title"=>'FileMan (ctrl+~)',
							"onclick"=>"BX('test_window').style.display='block';GetFileList2('','testfileman');",
							"class"=>'small_but'
							);	
$CustomButton['crfile']=Array(
							"msg"=>'будем создавть файл',
							"title"=>'создать файл',
							"onclick"=>"CreateFile('cfilename','path_fileman','testfileman')",
							"class"=>'small_but'
							);	
$CustomButton['upfile']=Array(
							"msg"=>'будем загружать файл',
							"title"=>'загрузить файл',
							"onclick"=>"BX('upload_file').style.display='block'",
							"class"=>'small_but'
							);	
$CustomButton['go']=Array(
							"msg"=>'перейти',
							"title"=>'перейти',
							"onclick"=>"GetFileList('path_fileman','testfileman');",
							"class"=>'small_but'
							);

							
							
//пункты контекстого меню						
$ContextMenu=Array(
		Array(
				'msg'=>"файл откроется на просмотр",
				'id'=>"view",
				'class'=>"menu",
				'aid'=>"v",
				'point_name'=>"view"
			),
			Array(
				'msg'=>"файл откроется на просмотр в UTF",
				'id'=>"viewu",
				'class'=>"menu",
				'aid'=>"vu",
				'point_name'=>"view utf"
			),
		Array(
				'msg'=>"файл откроется на редактирование",
				'id'=>"edit",
				'class'=>"menu2",
				'aid'=>"e",
				'point_name'=>"edit"
			),
			
		Array(
				'msg'=>"файл будет удалён",
				'id'=>"del",
				'class'=>"menu_del",
				'aid'=>"d",
				'point_name'=>"delete"
			),
		Array(
				'msg'=>"это архив и он будет распакован",
				'id'=>"unzip_",
				'class'=>"menu_unzip",
				'aid'=>"u",
				'point_name'=>"unpack"
			),
		Array(
				'msg'=>"скачается файл",
				'id'=>"down",
				'class'=>"menu_dw",
				'aid'=>"dw",
				'point_name'=>"download"
			),
	
);

foreach ($ContextMenu as $point)
$mainmenu.="var ".$point['id']."=BX('".$point['aid']."');\n";
		
	
//----------------------------------------------------------------------------------------------
///////////////////////end preparing///////////////////////////////////////////

//описание стилей окон
$DefaultWinStyle=Array(
                "width"=>'40%',
                "border"=>'3px solid #c3d0e9;',				
                "background"=>'#b7c8e8',
                "display"=>'none',
                "position"=>'absolute',
                "cursor"=>'hand',
                "left"=>"390px",
                "top"=>"100px",
                "padding"=>"5px",
                "z-index"=>1000,
                "is_moveable"=>'Y',
                "border-radius"=>'3px'
                );
$DefaultWinStyleSmall=Array(
                    "width"=>320,
                    "height"=>200,
                    "border"=>'1px solid black',
                    "background"=>'#FFF8DC',
                    "display"=>'block',
                    "position"=>'fixed',
                    "cursor"=>'hand',
                    "left"=>550,
                    "top"=>250,
                    "padding"=>5,
                    "z-index"=>1001,
                    "is_moveable"=>'Y',
                     "display"=>'none'				
                    );
					
$DefaultFieldStyle=Array(
                "width"=>1000,
                "height"=>660,
                "border"=>'1px solid #c3c6c9',
                "background"=>'#FFF8DC',
                "display"=>'block',
                "position"=>'fixed',
                "cursor"=>'hand',
                "left"=>350,
                "top"=>20,
                "padding"=>"20px",
                "z-index"=>10,
                "workcolor"=>"#EEE8AA",
                "border-radius"=>"3px"
                );

$WinStyleIBlock=Array(
					"width"=>320,
					"height"=>220,
					"border"=>'1px solid #c3c6c9',
					"background"=>'#FFF8DC',
					"display"=>'block',
					"position"=>'fixed',
					"cursor"=>'hand',
					"left"=>550,
					"top"=>250,
					"padding"=>5,
					"z-index"=>1001,
					"is_moveable"=>'Y',
					"border-radius"=>"3px"
					);
$WinStyleIpfs=Array(
					"width"=>'320px',
					//"height"=>'400px',
					"border"=>'1px solid #c3c6c9',
					"background"=>'#a5afd6',
					"display"=>'block',
					"position"=>'fixed',
					"cursor"=>'hand',
					"left"=>'75%',
					"top"=>'62%',
					"padding"=>5,
					"z-index"=>100,
					"is_moveable"=>'Y',
					"border-radius"=>"3px"
					);
$EditStyle=Array(
					"width"=>'70%',
					"height"=>'90%',
					"border"=>'1px solid #c3c6c9',
					"background"=>'#6699CC',
					"display"=>'block',
					"position"=>'fixed',
					"cursor"=>'defult',
					"left"=>350,
					"font-size"=>'14',
					"top"=>20,
					"color"=>"black",
					"padding"=>'10px',
					"z-index"=>10,
					"workcolor"=>"none",
					"is_moveable"=>'N',
					"fileman"=>'Y',
					"border-radius"=>"3px"
					);

//строим меню					
function BuildContextMenu()
{
	global $ContextMenu;
	echo '<table class="menu">';	
	foreach ($ContextMenu as $point):
		echo '<tr><td class=menu onmouseover=\'LightOn(this,"'.$point['msg'].'")\' onmouseout=LightOff() id="'.$point['id'].'"><a class="'.$point['class'].' point_menu" id="'.$point['aid'].'">'.$point['point_name'].'</a></td></tr>';
	endforeach;
	echo '</table>
	<iframe id="dwframe" style="display:none" src=""></iframe>';
}

//список файлов указаной директории					
function ShowFileSelect($listid='test',$Title='undefined',$dir,$ext='xml',$listsize=1,$DblClickAction='')
{
	$ifile=Array();
	if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$dir)) 
	{
		while (false !== ($file_1 = readdir($handle))) 
		{ 
			$file_ext=substr(strrchr($file_1, '.'), 1);
			if ($file_ext==$ext)
				$ifile[]=$file_1;
		}
	}
	asort($ifile);
     if ($ifile!=Array())
      {
		echo '<b style="font-size:10" align=\'left\'>'.$Title.'</b><br>';
		echo '<select style="width:100%;font-size:11;" size='.$listsize.' style="font-size:10" align=\'right\' id='.$listid.' onDblClick='.$DblClickAction.'>';
		$select=false;
		foreach ($ifile as $value):
			if ($select==false)
			{
				$select=true;
				echo '<option  selected  "value="'.$key.'">'.$value.'</option>';	
				continue;
			}
		echo '<option "value="'.$key.'">'.$value.'</option>';
		endforeach;
							echo '</select></br>';
	}
}

		
function ShowMenuWindow($ID,$NAME,$ShowHideSectionID,$content='')
{
    $menu="";
    
   $menu.='<table id='.$ID.' class=menu_table cellspacing=0 cellpadding=0>
           <tr><td>
        <b class="rtopwin">
    <b class="r1win"></b> <b class="r2win"></b> <b class="r3win"></b> <b class="r4win"></b>
    </b>
    </td></tr>
   <tr><td class=msection>
   <div style="background:#B9D3EE;position:relative;left:10;width:180;color:black">'.$NAME.'</div></td></tr>
   <tr><td class=menu_td>
   <div  id='.$ShowHideSectionID.'_ps style="background:white;padding:10" align=center>'.$content.'</div>
    </td></tr>
    <tr><td>
    <b class="rbottomwin">
    <b class="r4win"></b> <b class="r3win"></b> <b class="r2win"></b> <b class="r1win"></b>
    </b>
    </td></tr>
    </table>';
   echo $menu;
}

	
	
function AddButton($value,$mainmenu=false,$returnbutton=false,$MyButtons=false)
{
    global $MenuArray;
    global $CustomButton;
    if (is_array($MyButtons))
    $arButtons=$MyButtons;
    elseif($mainmenu==true)
    $arButtons=$MenuArray; else $arButtons=$CustomButton;
    $but=$value;
    if (!is_array($but)) 
    {
        $but=$arButtons[$value];
        if (!is_array($but)) return false;
    }

    $Button='<div class="'.$but['class'].'" align="center" OnClick="'.$but['onclick'].'" OnMouseOver="LightOn(this,\''.$but["msg"].'\');" OnMouseOut="LightOff();">'.$but['title'].'</div>';
    if ($returnbutton==false)
    echo $Button;
    else return $Button;
}
		
function AddWindow($NewId="newwindow",$NewName="NoNameWindow",$WorkID=false,$inner=false,$WinStyle=false,$buttons="",$mainmenu=false,$beforeInner='',$afterInner="")
{
    global $MenuArray;
    global $CustomButton;
    global $DefaultWinStyle;
    if (!is_array($buttons))
    $button=AddButton($buttons,$mainmenu,true);
    else 
    foreach ($buttons as $val)
    $button.=AddButton($val,$mainmenu,true);
    if (!$WinStyle)
    $WinStyle=$DefaultWinStyle;

    if (!$inner) 
       $inner="<div style='background-color:".$WinStyle['workcolor']."' id=".$WorkID."></div>".$button;
    if ($WinStyle['is_moveable']=='Y')
       $content.='<div id="'.$NewId.'" class="divwin_'.$NewId.' round_win"><b>'.$NewName.'</b><hr>
               <div class="closeButton" OnMouseOver="LightOn(this,\'закроется окно\');" OnMouseOut="LightOff();" onclick="Close(\''.$NewId.'\')">X</div>';
    else 
         $content.='<div id="'.$NewId.'" class="divwin_'.$NewId.' round_win"><b>'.$NewName.'</b><hr>';
    $content.=$beforeInner;
    if ($WinStyle['fileman']=='Y')
        $content.=AddButton('test_123',false,true);
    $content.=$inner.$afterInner;
    $content.='</div>';
    $content.='<style>.divwin_'.$NewId.'{';
   foreach ($WinStyle as $atr=>$value)
       $content.=$atr.':'.$value.';';	
   $content.='}</style>';				       
   $content.="<script>
		BX.ready(function()
		{
			dragMaster.makeDraggable(BX('".$NewId."'))
		});
   </script>";
   echo $content;
}
		
function AddField($NewId="newwindow",$NewName="NoNameWindow",$WorkID=false,$inner=false,$WinStyle=false,$buttons="",$mainmenu=false,$tableft=5)
{
    global $MenuArray;
    global $CustomButton;
    global $DefaultFieldStyle;
    if (!is_array($buttons))
        $button=AddButton($buttons,$mainmenu,true);
    else 
    foreach ($buttons as $val)
        $button.=AddButton($val,$mainmenu,true);
    if (!$WinStyle)
        $WinStyle=$DefaultFieldStyle;		
    $field_id=$NewId.'_field';
    $tab_id=$NewId.'_tab';
    $content="";
    $content.='<div id='.$NewId.'>';
    $content.='<div id='.$field_id.' style="width:980;padding:5;left:5;height:600;position:absolute;top:55;border:1px solid #00C5CD;z-index:99;">';
    $content.=$inner;
    $content.='</div>';
    $content.='<b><div id='.$tab_id.' onmousedown="ShowField(this,\''.$field_id.'\');" 
    style=" 
    position:absolute;
    left:'.$tableft.';
    height:15;
    top:28;
    border-top:1px solid #00C5CD;
    border-right:1px solid #00C5CD;
    border-left:1px solid #00C5CD;
    border-bottom:2px solid #FFF8DC;
    background:#FFF8DC;
    padding:5;
    margin:0;
    width:100;
    z-index:100;">'.$NewName.'</div></b>';
    $content.='</div>';
    $content.='<script>				
    var old_node = BX("'.$NewId.'");
    var oldparentNode = BX("'.$NewId.'").parentNode;
    // alert(oldparentNode);
    var clone = old_node.cloneNode(true);
    var newparentNode = BX("'.$WorkID.'").appendChild(clone);
    oldparentNode.removeChild(old_node);
    </script>';	
    echo $content;
}
//удаление скрипта 
if (@$_GET['delete']=="Y")
{
    header("Content-type:text/html; charset=windows-1251");
    unlink(__File__);
    echo "<div style='background-color:#B9D3EE;
       border:1px solid red;
       text-align:center;
       color:red;
       height:30;
       z-index:10000;'> Файла теперь нет - он удалён!</div>";
    die();
}

$UPLOAD_DIR="/".COption::GetOptionString("main", "upload_dir");
$interval=COption::GetOptionString("catalog", "1C_INTERVAL", "-");
if ((!$USER->IsAdmin())&&(@($_GET['mode']!='query'))) 
{
    echo 'Доступ запрещён. Вы не администратор сайта. До свидания.'; 
    localredirect("/404.php");
}


error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
header("Content-type:text/html; charset=windows-1251");
if (@$_GET['action']=="addfield")
{	
    AddField('test_123','test','para1','test',false,false,false,5);
    AddField('testfield2','offers.xml','para1','test2',false,false,false,120);
    die();
}

if (@$_GET['action']=="createfile")
{	
    if (file_exists($_SERVER['DOCUMENT_ROOT'].$_GET['path']))
    {
        echo 'error001'; 
        die();
    }

    if ($_GET['isdir']=='Y')
    {
        if (mkdir($_SERVER['DOCUMENT_ROOT'].$_GET['path'], 0, true))
            echo 'success'; else echo 'fail';
    }
    else
    {					
        if ($f = fopen ($_SERVER['DOCUMENT_ROOT'].$_GET['path'], 'a+'))
           echo 'success'; else echo 'fail';
        fclose($f);
    }
    die();
}

if (@$_GET['action']=="change_time"):?>
    <table align='center'>
    <tr>
    <th class="th_table">Путь</th>
    <th class="th_table2"><input id='path1' type="text" size="30" value="<?if(isset($_POST['path1'])) echo $_POST['path1']; else echo "<?=$catalog_import_path?>"?>" name="path1"></th>
    </tr>
    <tr>
    <th class="th_table">Дата </th>
    <th class="th_table2"><input id='date_e' type="text" size="30" value="<?if(!$_POST['date']=='') echo $_POST['date']; else echo $date;?>" name="date_e"></th>
    </tr>
    <tr><td COLSPAN=2 align="center">
    <?AddButton('change1');?>
    </td></tr>
    </table>
    <?die();
endif;
if (@$_GET['action']=="createiblocktypeform")
{
    $inner='<div id="successiblock"></div>
    Введите ID типа инфоблока:<br>
    <input id="iblocktype" size=45 value="support_test_iblock_type"><br>'.
    'Выгружать в этот тип инфоблока <input type="checkbox" id="1ciblock" checked>'.AddButton('iblockbut',false,true);
    AddWindow('iblock','Создание типа инфоблока',false,$inner,$WinStyleIBlock);
    die();
}		

			
if (@$_GET['action']=="createiblocktype")
{
CModule::IncludeModule('iblock');
$arFields = Array(
    'ID'=>$_GET['iblocktype'],
    'SECTIONS'=>'Y',
    'IN_RSS'=>'N',
    'SORT'=>100,
    'LANG'=>Array(
            'en'=>Array(
                    'NAME'=>'Catalog',
                    'SECTION_NAME'=>'Sections',
                    'ELEMENT_NAME'=>'Products'
                    )
            )
    );

$obBlocktype = new CIBlockType;
$DB->StartTransaction();
$res = $obBlocktype->Add($arFields);
if(!$res)
{
    $DB->Rollback();
    echo '<div style="color:red;border:1px dashed red;padding:5">'.$obBlocktype->LAST_ERROR;
}
else
{
    echo '<div style="color:green;border:1px dashed green;padding:5">Тип инфоблока создан успешно!';
    $DB->Commit();
}		
if (@$_GET['USE_IBLOCK_TYPE']=='Y')
{
    COption::SetOptionString("catalog",'1C_IBLOCK_TYPE', $_GET['iblocktype']);
    COption::SetOptionString("catalog", "1C_USE_IBLOCK_TYPE_ID", "Y");
    echo 'Каталог будет выгружаться в тип инфоблока '.$_GET['iblocktype'].'</div></br>';
} 
else 
    echo '</div></br>';					
die();
}

if (@$_GET['action']=='getstep') 
{
    echo $_SESSION["BX_CML2_IMPORT"]["NS"]["STEP"];
    die();
}

if (@$_GET['action']=='download')
{
    $filename=$_SERVER["DOCUMENT_ROOT"].$_GET['path'].$_GET['file'];
    $mimetype='application/octet-stream';
    if (file_exists($filename)) {
        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');
        header('Content-Type: ' . $mimetype);
        header('Last-Modified: ' . gmdate('r', filemtime($filename)));
        header('ETag: ' . sprintf('%x-%x-%x', fileinode($filename), filesize($filename), filemtime($filename)));
        header('Content-Length: ' . (filesize($filename)));
        header('Connection: close');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '";');
        echo file_get_contents($filename);
    } else {
        header($_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        header('Status: 404 Not Found');
    }
    exit;
}

if(@$_POST['action']=="deletefile")
{
	if (!$_POST['fullpath'])
		$path=$_SESSION['bx_1c_import']['path'];
	else
		$path="";
	if (is_dir($_SERVER['DOCUMENT_ROOT'].$path.$_POST['filename']))
        $res=rmdir($_SERVER['DOCUMENT_ROOT'].$path.$_POST['filename']);
    else
        $res=unlink($_SERVER['DOCUMENT_ROOT'].$path.$_POST['filename']);
	if ($res)
		echo 'success';
	else
		echo 'error';
    die();
}

if(@$_GET['action']=="getfiles")
{
    if (!isset($_GET['path'])) 
            $urlpath='/'; else $urlpath=$_GET['path'];
    $realpath=str_replace('//','/',$urlpath.'/');
    $_SESSION['bx_1c_import']['path']=$realpath;
    @$_SESSION['bx_1c_import']['filter']=$_GET['like_str'];
    if (isset($_GET['workarea']))
            $wa=$_GET['workarea']; else $wa="minifileman";
    $rows=400;
    $cols=1;   
    $dirs=explode('/',$realpath);
    $i=1;
    $full="";
    $el['DIR']='[root]';
    $el['PATH']='/';
    $cat[]=$el;
    while ($i<=count($dirs))
    {
        $el=Array();
        $el['DIR']=$dirs[$i];
        if ($dirs[$i]!='')
        {
                $el['PATH']=$full.$dirs[$i].'/';
                $full.=$dirs[$i].'/';
                $cat[]=$el;
        }
        $i++;
    }
    $link_path="/";
    $id=0;$l=1;
    echo '<div style="font-size:11px;background:#d8dcf0;padding:4px;">';
    foreach ($cat as $el_d)
    {
        $id="p_".$wa.'_'.$l++;
        $func=str_replace('//','/','/'.$el_d["PATH"]);?><a id="<?=$id?>"  OnMouseOver="LinkLightOn('<?=$id?>','#1C1C1C');" OnMouseOut="LinkLightOff();" href="javascript:GetFileList2('<?=$func;?>','<?=$wa?>')"><?=$el_d["DIR"]?></a>/<?
    }
    echo '</div>';
    echo '<div style="overflow:auto;height:200px;width:100%;background:white;">';
    echo '<table style="font-size:9;width:100%">';
    if ($handle = opendir($_SERVER['DOCUMENT_ROOT'].$_GET['path'])) 
    {
            $i=-1;
            $col=0;
            $fun_str="";
            $q=$_GET['like_str'];
            $IfoundFiles=false;
            if ($q=='') {$q="all";$fun_str="all";}
            $id=0;$l=1;
            $mdir=Array();
            $mfile=Array();
            echo "<tr><td valign='top' style='font-size:12;width:50%;border-right:1px solid #DCDCDC;'>";
            while (false !== ($file_1 = readdir($handle))) 
            { 
                if (is_dir($_SERVER['DOCUMENT_ROOT'].$_GET['path'].'/'.$file_1)):
                        $mdir[]=$file_1;
                else:
                        $mfile[]=$file_1;
                endif;
            }
            sort($mdir);
            sort($mfile);
            $mdirectory=array_merge($mdir,$mfile);
            $color='#FFF8DC';
            foreach ($mdirectory as $file)
            {		
                if ($color=='#FFF8DC') $color='#EEEEE0'; else $color='#FFF8DC';
                if(($file!==".")&&($file!=="..")&&(strpos($file.$fun_str,$q)!==false))
                {
                    $id="f_".$wa.'_'.$l++;
                    if ($i>$rows) {if(++$col==$cols) 
                            break; 
                    elseif($IfoundFiles==true)
                    {
                            echo '</td><td width=200 valign="top" style="font-size:9">';$IfoundFiles=false;}$i=1;
                    }
                    $IfoundFiles=true;?>

                    <div width=100% style="background:<?=$color?>;">
                    <?

                    if (is_dir($_SERVER['DOCUMENT_ROOT'].$_GET['path'].'/'.$file)):?>

                            <img src='/bitrix/images/fileman/folder.gif'> <a id="<?=$id?>" style="font-size:12;color:blue;" OnMouseOver="LinkLightOn('<?=$id?>','#363636');" OnMouseOut="LinkLightOff();" href="javascript:GetFileList2('<?=str_replace('//','/',$_GET['path'].'/').$file.'/'?>','<?=$wa?>')"><?=$file?></a>
                    <?else:?>

                    <img src='/bitrix/images/fileman/file.gif'>
                    <a id="<?=$id?>" style="font-size:12;" href="javascript:ShowFile('<?=$file?>','<?=$realpath?>','N')" oncontextmenu="return ShowMenu(event);" OnMouseOver="LinkLightOn('<?=$id?>','#1C1C1C');" OnMouseOut="LinkLightOff();" href="#" onmousedown="moveState = false;" onmousemove="moveState = false;"><?if (strlen($file)>50) echo substr($file,0,-(strlen($file)-8))."...".substr($file,-4); else echo $file;?></a>
                    <a  style="color:red;font-size:10;" href=javascript:Delete('<?=$file?>','<?=$wa?>') OnMouseOver="LightOn(this,'! удалится <b> <?=$file?></b> !');" OnMouseOut=LightOff()>[X]</a><a style="color:green;font-size:10;" href=javascript:ShowInfo('<?=$realpath.$file?>') OnMouseOver="LightOn(this,'отобразится информация по <b> <?=$file?></b>');" OnMouseOut=LightOff()>[!]</a>
                    </div>	
                    <?endif;?>
                    <?
                 }
                 $i++;
            }
            closedir($handle); 
    }
    echo '</td></tr></table>';
    echo '</div>';
    die();
}
//распаковка файла 
if (@$_POST['action']=="unzip")
{
    $zip = $_POST['filename'];
    CModule::IncludeModule('iblock');
    $result = CIBlockXMLFile::UnZip($zip);
    echo 1;
    die();		
}

//грузим  любой файл в указанную папку
if (@$_GET['upload']=="Y")
{
    if(is_array($_FILES['test_file']))
    {
        $tmp_name=$_FILES['test_file']['tmp_name'];
        if( $_SESSION['bx_1c_import']['path']=="")
        $test_file=$UPLOAD_DIR."/".$_FILES['test_file']['name']; 
        else $test_file=$_SESSION['bx_1c_import']['path'].$_FILES['test_file']['name']; 
        if(is_uploaded_file($tmp_name))
                        {
                                move_uploaded_file($tmp_name,$_SERVER['DOCUMENT_ROOT'].$test_file);
                                echo("<a href='".$test_file."' target='_blank'>".$_FILES['test_file']['name']."</a>");
                        }
        else
                echo "error";
                echo '<br>';
    }
    //форма для загрузки файла на сервер
    if (isset($_POST['test_file']))
       echo "Файл ".$_POST['test_file']." загружен";
    echo "<div style='background-color:#FFE4B5'>
    <form action='".$script_name."?upload=Y' method=post enctype='multipart/form-data'>
    <input onmousedown='moveState = false;' onmousemove='moveState = false;' type='file' name='test_file'>
    <input type='submit' value='загрузить' name='upload_file'>
    </form></div>
    ";
    die();
}
//поиск элемента в файле и на сайте по XML_ID
if (isset($_GET['search']))
{
    $import=file_get_contents($_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/1c_catalog/import.xml");
    $import=$APPLICATION->ConvertCharset($import,"UTF-8","WINDOWS-1251");
    $q=$_GET['search'];
    $import=str_replace('/',"",$import);
    preg_match('/<Товар>.*.<Ид>'.$q.'<Ид>.*.<ПолноеНаименование>/is', $import , $product);
    if(count($product)) $ISaLive["file"]['here']="Y"; else $ISaLive["file"]['here']="N";
    CModule::IncludeModule("iblock");
    $check=CIBlockElement::GetList(Array(),Array("EXTERNAL_ID"=>$q));
    if (!$check) echo 'на сайте таких нет';
    while($res=$check->Fetch())
    echo 'IBLOCK_ID='.$res["IBLOCK_ID"].' <a href="/bitrix/admin/iblock_element_edit.php?ID='.$res["ID"].'&IBLOCK_ID='.$res["IBLOCK_ID"].'&type='.$res["IBLOCK_TYPE_ID"].'" target="_blank">Перейти</a><br>';
    die();
}
	
//получение  текста xml-файла, который будет переправлен с сайта в 1С при следующем обмене.
if($_GET["mode"] == "query")
{
    CModule::IncludeModule("sale");
    $arParams=Array(
    "SITE_LIST" => COption::GetOptionString("sale", "1C_SALE_SITE_LIST", ""),
    "EXPORT_PAYED_ORDERS" => COption::GetOptionString("sale", "1C_1C_EXPORT_PAYED_ORDERS", ""),
    "EXPORT_ALLOW_DELIVERY_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_ALLOW_DELIVERY_ORDERS", ""),
    "EXPORT_FINAL_ORDERS" => COption::GetOptionString("sale", "1C_EXPORT_FINAL_ORDERS", ""),
    "FINAL_STATUS_ON_DELIVERY" => COption::GetOptionString("sale", "1C_FINAL_STATUS_ON_DELIVERY", "F"),
    "REPLACE_CURRENCY" => COption::GetOptionString("sale", "1C_REPLACE_CURRENCY", ""),
    "GROUP_PERMISSIONS" => explode(",", COption::GetOptionString("sale", "1C_SALE_GROUP_PERMISSIONS", "")),
    "USE_ZIP" => COption::GetOptionString("sale", "1C_SALE_USE_ZIP", "Y"));
    $arFilter = Array();
    if($arParams["EXPORT_PAYED_ORDERS"])
        $arFilter["PAYED"] = "Y";
    if($arParams["EXPORT_ALLOW_DELIVERY_ORDERS"]<>"N")
        $arFilter["ALLOW_DELIVERY"] = "Y";
    if(strlen($arParams["EXPORT_FINAL_ORDERS"])>0)		
    {
        $bNextExport = false;
        $arStatusToExport = Array();
        $dbStatus = CSaleStatus::GetList(Array("SORT" => "ASC"), Array("LID" => LANGUAGE_ID));
        while ($arStatus = $dbStatus->Fetch())
        {
            if($arStatus["ID"] == $arParams["EXPORT_FINAL_ORDERS"])
                $bNextExport = true;
            if($bNextExport)
                $arStatusToExport[] = $arStatus["ID"];
        }
        $arFilter["STATUS_ID"] = $arStatusToExport;		
    }
    if(strlen($arParams["SITE_LIST"])>0)
        $arFilter["LID"] = $arParams["SITE_LIST"];
    if(strlen(COption::GetOptionString("sale", "last_export_time_committed_/bitrix/admin/1c_excha", ""))>0)
    $arFilter[">=DATE_UPDATE"] = ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_/bitrix/admin/1c_excha", ""), "FULL");
    ob_start();
    CSaleExport::ExportOrders2Xml($arFilter, false, $arParams["REPLACE_CURRENCY"]);
    $xml=ob_get_contents();
    ob_end_clean();
    $dres=CSite::GetList();
    $site=$dres->Fetch();
    if (strtoupper($site['CHARSET'])<>'WINDOWS-1251')
    $xml=$APPLICATION->ConvertCharset($xml,$site['CHARSET'],"WINDOWS-1251");
    if (@$_GET['save']=='Y')
    {
        unlink($_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/bx_orders.xml");
        $f = fopen ($_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/bx_orders.xml", 'a+');
        fwrite ($f,$xml);
        fclose($f);
        $xml=trim($xml);
        echo '<pre style="background:white; text-align:right">текст xml-файла, который будет передан в 1С при следующем обмене</pre>';
        echo '<div onmousedown="moveState = false;" onmousemove="moveState = false;" style="overflow-y:scroll;height:90%;width:100%;background:white;">';
        highlight_string($xml);
        echo '</div>';
    }
    else echo $xml;
    die();
}

if ($_GET["action"]=="show_bxmltree")
{
    CModule::IncludeModule('iblock');
    $xmlfile=new CIBlockXMLFile;
    $dbres=$xmlfile->GetList();
    if (!$dbres)
       die();
    echo '<div  style="overflow:auto;height:100%;width:100%;">';
    echo '<table cellspacing=2 cellpadding=5 style="border:0px solid #E6E6FA;font-size:11px;background:white;">';
    echo '<tr style="background:grey;color:white;">';
    echo '<td>'.'ID'.'</td>';
    echo '<td>'.'PARENT_ID'.'</td>';
    echo '<td>'.'LEFT_MARGIN'.'</td>';
    echo '<td>'.'RIGHT_MARGIN'.'</td>';
    echo '<td>'.'DEPTH_LEVEL'.'</td>';
    echo '<td>'.'NAME'.'</td>';
    echo '<td>'.'VALUE'.'</td>';
    echo '<td>'.'ATTRIBUTES'.'</td>';
    echo '</tr>';

    while($res=$dbres->Fetch())
    {
        echo '<tr>';
        foreach ($res as $value):
        echo '<td valign=top  style="width:50px;border:1px solid #E6E6FA">'.$APPLICATION->ConvertCharset($value,SITE_CHARSET,"windows-1251").'</td>';
        endforeach;
        echo '</tr>';
    }
    echo '</table>';
    echo '<div>';
    die();
}
	
//вывод содержимого файлов
if ($_GET["mode"]=="show_xml")
{
    $filename=$_SERVER['DOCUMENT_ROOT'].$_GET["path"].$_GET["file"];
    echo '<pre style="background:white; text-align:left">Редактировать: <a href="/bitrix/admin/fileman_file_edit.php?path='.$_GET["path"].$_GET["file"].'&full_src=Y" target="_blank">'.$filename.'</a></pre>';
    if (isset($_GET["path"]))  
        $filename=$_SERVER['DOCUMENT_ROOT'].$_GET["path"].$_GET["file"]; else	 
    $filename=$_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/1c_catalog/".$_GET["file"];
    $file_ext=substr(strrchr($filename, '.'), 1);
    if (in_array($file_ext,$APicture))
    {
        echo "<img src='".$_GET["path"].$_GET["file"]."'>";  
        die();
    }
    $xml = file_get_contents($filename);
    if (!$xml) 
        echo "Нет такого файла";
    if(@$_GET['isutf']=='Y')
        $xml=$APPLICATION->ConvertCharset($xml,"UTF-8","windows-1251");
    elseif (ToUpper(SITE_CHARSET)!='WINDOWS-1251')
    $xml=$APPLICATION->ConvertCharset($xml,SITE_CHARSET,"windows-1251");

    function callback($buffer)
    {
        if (round(filesize($_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/1c_catalog/".$_GET['offers'])/1024,2)<2000)
        {
            $pattern=Array('/Товар/','/ЗначенияСвойства/');
            $replacements=Array("<b style='color:red'>Товар</b>","<b style='color:green'>ЗначенияСвойства</b>");
            $buffer=preg_replace($pattern, $replacements, $buffer);
        }
        if (!$f=fopen($_SERVER['DOCUMENT_ROOT'].$_GET["path"].$_GET["file"],'a')) 
            $WriteError="<p style='font-size:10px;color:red;'>Открыть на запись файл не удастся!</p>";
        fclose($f);
        return '<div onmousedown="moveState = false;" onmousemove="moveState = false;" style="overflow:auto;height:90%;width:100%;background:white;">'.$buffer.'</div>';
    }
    ob_start("callback");
    highlight_string($xml);
    ob_end_flush();
    die();
}
	
if ($_GET["mode"]=="edit")
{

if (isset($_GET["path"]))  
    $filename=$_SERVER['DOCUMENT_ROOT'].$_GET["path"].$_GET["file"]; else	 
$filename=$_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/1c_catalog/".$_GET["file"];
echo '<pre style="background:white; text-align:right"><a href="/bitrix/admin/fileman_file_edit.php?path='.$_GET["path"].$_GET["file"].'&full_src=Y" target="_blank">'.$filename.'</a></pre>';

    $file_ext=substr(strrchr($filename, '.'), 1);
    if (in_array($file_ext,$APicture))
    {
    echo "<img src='".$_GET["path"].$_GET["file"]."'>";  die();
    }
    $xml = file_get_contents($filename);

    if (!$xml) echo "Нет такого файла";
    if(@$_GET['isutf']=='Y')
            $xml=$APPLICATION->ConvertCharset($xml,"UTF-8","windows-1251");
    elseif (ToUpper(SITE_CHARSET)!='WINDOWS-1251')
            $xml=$APPLICATION->ConvertCharset($xml,SITE_CHARSET,"windows-1251");
    ?>


    <div id="sfstatus" onmousedown="moveState = false;" onmousemove="moveState = false;" style="display:none;color:green;border:1px dashed green;padding:5; text-align:center;width:250px;margin:5"></div>
    <table>
    <tr>
            <td>
                    <div onmousedown="moveState = false;" onmousemove="moveState = false;" id="savefile" align="center"  onclick="SaveFile('<?=$_GET["path"].$_GET["file"]?>')" OnMouseOver="LightOn(this,'сделанные изменения будут сохранены');" OnMouseOut="LightOff()"; class="small_but">Сохранить</div>
            </td>
            <td>
                    <div onmousedown="moveState = false;" onmousemove="moveState = false;" id="viewfile" align="center"  onclick="ShowFile('<?=$_GET["file"]?>','<?=$_GET["path"]?>','N')" OnMouseOver="LightOn(this,'переход в режим просмотра текущего файла');" OnMouseOut="LightOff()"; class="small_but">Посмотреть</div>
            </td>
    </tr>
    </table>
    <?
    echo '<textarea onmousedown="moveState = false;" onmousemove="moveState = false;" id="textfile" rows="30" cols="119" style="position:absolute;overflow:auto;font-size:11pt;height:82%;width:98%;">'.htmlspecialchars($xml).'</textarea>';
    die();
}
	
//save
	if ($_REQUEST["action"]=="save")
	{
	    $filename=$_SERVER['DOCUMENT_ROOT'].$_REQUEST["filename"];
		$f = fopen($filename, 'w+');
		if (ToUpper(SITE_CHARSET)!='UTF-8')
			$text=$APPLICATION->ConvertCharset($_REQUEST["text"],'UTF-8',SITE_CHARSET);
		if (($f)&&(fwrite($f, $text)!=false))
		echo 'OK'; else echo 'error';
		fclose($f);
		die();
	}
//проверка файла,  не существует или нет прав на чтение?
if (@$_GET['check_file']=="Y")
{
    unset($_SESSION["BX_CML2_IMPORT"]);
    $c=0;
    if(file_exists($_SERVER['DOCUMENT_ROOT'].$UPLOAD_DIR."/1c_catalog/".$_GET['file'])) $c=$c+2;
    else $c=$c+3;
    if($c==2)
        echo "<div style='width:270;font-size:11;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;background-color:FA8072;padding:5'>Нет прав на чтение файла!</div>";
    if ($c==3)
        echo "<div style='width:270;font-size:11;border-left:1px solid black;border-right:1px solid black;border-bottom:1px solid black;background-color:FA8072;padding:5'>Файла ".$_GET['file']." не сущестует!</div>";
    die();
}

$items[0]=Array();
$group[0]=Array();
$c_offers[0]=Array();

//получение  информации по количеству групп, товаров и предложений, путём анализа файлов каталога и предложений
if (@$_GET['info']=="Y")
{
    $content=file_get_contents($_SERVER['DOCUMENT_ROOT'].$_GET['file']);
    $offer=iconv("WINDOWS-1251", "UTF-8", '<Предложение>');
    //	$product=iconv("WINDOWS-1251", "UTF-8", '<Товар>');
    $section=iconv("WINDOWS-1251", "UTF-8", '<Группа>');
    preg_match_all('/'.$product.'/', $content , $items);
    preg_match_all('/'.$section.'/', $content , $group);
    preg_match_all('/'.$offer.'/', $content, $c_offers);
    $file_size=round(filesize($_SERVER['DOCUMENT_ROOT'].$_GET['file'])/1024,2);
    ?>

    <table style="font-size:11;" cellpadding="0"><tr><td align="right">Размер файла: </td><td><b><?=$file_size.' kb';?></b> | </td>
    <td align="right" >Предложений: </td><td><b><?=count($c_offers[0]);?></b> | </td>
    <td align="right">Товаров: </td><td><b><?=count($items[0]);?></b> | </td>
    <td align="right">Групп: </td><td><b><?=count($group[0]);?></b></td></tr>
    </table>
    <?	die();
}

//смена метки времени последнего обмена 
if (!$_REQUEST['path1']==''):
    $path_companent = substr($_REQUEST['path1'], 0, 22); 
    $full_path=$_REQUEST['path1'];
else: 
    $path_companent = substr("<?=$catalog_import_path?>", 0, 22);
    $full_path="<?=$catalog_import_path?>";
endif;

if((!$_REQUEST['date']=='')&&(isset($_REQUEST['change'])))
{
    if (!file_exists("bx_exchange_date.log"))
    {
        $f = fopen ("bx_exchange_date.log", 'a+');
        fwrite ($f, ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_".$path_companent, ""), "FULL"));
        fclose($f);
    }
    COption::SetOptionString("sale", "last_export_time_committed_".$path_companent, MakeTimeStamp($date, "DD.MM.YYYY HH:MI:SS"));
}

$date=ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_".$path_companent, ""), "FULL");
if (isset($_REQUEST['AJAX'])) 
{
	echo $date;
	die();
}

//получнеие списка заказов, которые будут выгружены в 1с при следующем обмене
if (isset($_REQUEST['check'])):
	CModule::IncludeModule("sale");
	$path_companent = substr($_REQUEST['path'], 0, 22);
	if(isset($_REQUEST['PAYED'])) 
		$arFilter['PAYED']="Y";
	if(isset($_REQUEST['ALLOW_DELIVERY'])) 
		$arFilter['ALLOW_DELIVERY']="Y";
	$arFilter[">=DATE_UPDATE"] = ConvertTimeStamp(COption::GetOptionString("sale", "last_export_time_committed_".$path_companent, ""), "FULL");
	$change=false;
	$dbOrderList = CSaleOrder::GetList(
						array("ID" => "DESC"),
						$arFilter,
						false,
						$count,
						array("ID", "LID", "PERSON_TYPE_ID", "PAYED", "DATE_PAYED", "EMP_PAYED_ID", "CANCELED", "DATE_CANCELED", "EMP_CANCELED_ID", "REASON_CANCELED", "STATUS_ID", "DATE_STATUS", "PAY_VOUCHER_NUM", "PAY_VOUCHER_DATE", "EMP_STATUS_ID", "PRICE_DELIVERY", "ALLOW_DELIVERY", "DATE_ALLOW_DELIVERY", "EMP_ALLOW_DELIVERY_ID", "PRICE", "CURRENCY", "DISCOUNT_VALUE", "SUM_PAID", "USER_ID", "PAY_SYSTEM_ID", "DELIVERY_ID", "DATE_INSERT", "DATE_INSERT_FORMAT", "DATE_UPDATE", "USER_DESCRIPTION", "ADDITIONAL_INFO", "PS_STATUS", "PS_STATUS_CODE", "PS_STATUS_DESCRIPTION", "PS_STATUS_MESSAGE", "PS_SUM", "PS_CURRENCY", "PS_RESPONSE_DATE", "COMMENTS", "TAX_VALUE", "STAT_GID", "RECURRING_ID")
					);
?>
	<div style="font-size:12;padding:3;background: white;"> Дата последнего обмена - <?=$arFilter[">=DATE_UPDATE"]?></div>
	<br>
<?	$n=0;
	echo '<div style="font-size:11;padding:3;background: white;">';
	while($arOrder = $dbOrderList->Fetch())
	{
		$n++;
		echo '<a href="/bitrix/admin/sale_order_detail.php?ID='.$arOrder["ID"].'" target="_blank" >Заказ №'.$arOrder["ID"].'</a>';
		echo ' - дата именения ',$arOrder["DATE_UPDATE"];
		echo '<br>';	
		$change=true;
	}
	if (!$change) echo "На сайте нет заказов, изменённых после даты последнего обмена с 1С!!!";
	echo '<br><b>ВСЕГО ЗАКАЗОВ: '.$n.'</b><br>';
	echo "</div>";
	die();
endif;
if (isset($_REQUEST['setstep']))
{
	$_SESSION["BX_CML2_IMPORT"]["NS"]["STEP"]=IntVal($_REQUEST['setstep']);
	echo $_SESSION["BX_CML2_IMPORT"]["NS"]["STEP"];
	die();
}
unset($_SESSION["BX_CML2_IMPORT"]);//сброс  шага импорта
$host='http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];//хост
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html>
<head>
<?CUtil::InitJSCore(Array('ajax','window'));
$APPLICATION->ShowHeadScripts();
$APPLICATION->ShowHeadStrings();
?>
<style>


body 
{
	background:white;
	font-family:tahoma,verdana,arial,sans-serif,Lucida Sans;
	font-size:12px;
	padding:0px;
	margin:0px;
	margin-left:5px;
}
		
.button
{
   background-color:#B9D3EE;
   border:1px solid #ADC3D5;
   width:150;
   height:20px;
   font-size:13;
   color:#2B587A;
   padding-top:5px;
   padding-bottom:2px;
   margin:4px;
}

   
.button2
{
   background-color:#B9D3EE;
   border:none
   cursor:hand;
   text-align:center;
   width:150;
   height:20px;
   font-size:12;
   color:#2B587A; 
   margin:5;
   padding-top:5;
   box-shadow:0px 0px 3px 0px #c3c6c9;
    -webkit-box-shadow:0px 0px 3px 0px #c3c6c9;
    -moz-box-shadow:0px 0px 3px 0px #c3c6c9;
	border-radius:5px;

}   

   
.rtopwin, .rbottomwin{display:block;width:200;}
.rtopwin *,.rbottomwin *{display: block; height: 1px; overflow: hidden;background:#B9D3EE;}
.r1win{margin: 0 5px;}
.r2win{margin: 0 3px;}
.r3win{margin: 0 2px;}
.r4win{margin: 0 1px; height: 2px;}

.msection
{
	width:180;
	font-size:14;
	color:white;
	border-top:none;
	background:#B9D3EE;
	border-bottom:6px solid #B9D3EE;
}

   
.FrontTab
{
  position:absolute;
  height:15;
  top:30;
  border-top:1px solid black;
  border-right:1px solid black;
  border-left:1px solid black;
  border-bottom:2px solid #FFF8DC;
  background:#FFF8DC;
  padding:5;
  margin:0;
  width:100;
  z-index:100;
}
   
.message
{
   background-color:#B9D3EE;
   border:2px solid red;
   text-align:center;
   position:absolute;
   height:15px;
   padding:10;
   left:40%;
   top:50%;
   opacity:0.5;
   font-size:12;
   color:red;
   z-index:10000;
}

.tab
{
	border-top-left-radius:5px;
	border-top-right-radius:5px;
	-moz-border-top-left-radius:5px;
	-moz-border-top-right-radius:5px;
	border: 1px solid #d3e1fa;
	padding-left:10px;
	padding-right:10px;
	padding-top:5px;
	padding-bottom:5px;
	font-size:12px;
	margin-left:2px;
	float:left;
	background: #d3e1fa;
	cursor:pointer;
}

.ver
{
	border-radius:5px;
	border-bottom-right-radius:5px;
	-moz-border-bottom-left-radius:5px;
	-moz-border-bottom-right-radius:5px;
	border: 1px solid #E7ECF5;
	padding:5px;
	float:right;
	font-size:10px;
	margin-left:2px;
	background: #E7ECF5;
	position:absolute;
	right:2%;
	top:2%;
}

.left_panel
{
	border-top-right-radius:5px;
	border-bottom-right-radius:5px;
	-moz-border-top-left-radius:5px;
	-moz-border-top-right-radius:5px;
	border: 1px solid #d3e1fa;
	padding-left:10px;
	padding-right:10px;
	float:left;
	right:0px;
	max-width:140px;
	padding-top:5px;
	padding-bottom:5px;
	font-size:12px;
	margin-left:2px;
	background: #d3e1fa;
	cursor:pointer;
}

.file_panel
{

	border: 1px dotted #E7ECF5;
	font-size:12px;
	padding:5px;
	background:white;

}

   
.divwin 
{
	width: 300px;
	background: #a3b3cf;
	display: none;
   	cursor:hand;
	left:320px;
	top:160px;
}

.divwin_times 
{
	width: 300px;
	background: #a5afd6;
	display: none;
	left:50%;
	top:50%;
}

.divwin_param 
{
	width: 300px;
	background: #a3b3cf;
	display: none;
	left:50%;
	top:50%;
}  
 
.divwin_stepdiag
{
	background: #a5afd6;
	display: none;
	left:50%;
	top:50%;
}

.divwin_orderlist
{
	width: 300px;
	background: #a5afd6;
	display: none;
	left:70%;
	top:400px;
}

.divwin_info
{
	width: 320px;
	background: #a5afd6;
	display: none;
	left:7px;	
	top:60%;
}

.divwin_main
{
	border: 1px solid black;
	background: #a5afd6;
	display: block;
	left:10px;
	top:20px;
}

.divwin_custom
{
	width: 500px;
	background: #a5afd6;
	display: block;
	left:225px;
	top:8px;
}

.round_win
{
	border-radius:3px;
	padding:10px 5px 5px 5px;
	position: fixed;
	cursor:default;
	border: 2px solid #c3d0e9;
	z-index:100;
	font-size:12px;
}

.auth_form 
{

	background:white;
	font-size:11px;
	padding:5px;
	width:30%;
	
}

.import_form
{
right:0px;
float:right;

background:white;
	font-size:11px;
	padding:5px;
	width:30%;

}

.auth_field
{
	font-size:11px;
	clear:both;
	width:99%;

}

.but 
{
	border: 1px solid Gray;
	background-color: #DCDCDC;
	padding: 1px 1px 1px 1px;
	margin:1px;
	font-size:12;
	width:150px;
	color:black;
	align:center;
}

.small_but
{

	background-color: white;
	padding:3px;
	width:130px;
	font-size:11px;
	color:black;
	margin:2px;	
	border:1px dotted #dae1ed;

}

.small_but:hover
{
		background-color: #e9edf5;
	 box-shadow:inset 0px 0px 4px 0px white;
    -webkit-box-shadow:inset 0px 0px 4px 0px white;
    -moz-box-shadow:inset 0px 0px 4px 0px white;
}

.closeButton {
	position: absolute;
	top: 0px;
	right: 0px;
	font-weight: bold;
	font-family:Tahoma;
	cursor: pointer;
	z-index:250;
	background: white;
	padding: 2px 5px 2px 5px;
	border-radius:1px;
	margin:3px;
}

.sysbutton
{
	position: absolute;
	top: 2px;
	right: 2px;
	font-size:12;
	border: 1px solid gray;
	cursor: pointer;
	z-index:250;
	background: white;
	padding: 2px 4px 2px 4px;
}

.main_div 
{
	width:25%;
   background-color:#FFE4B5;
   border:1px solid #ADC3D5;
   text-align:center;
   position:fixed;
   left:74%;
   top:45px;
   font-size:11;
}
   
.main_table 
{
   width:50%;
   text-align:center;
}

.th_table 
{ 
	border:1px solid #ADC3D5;
	text-align:right;
	font-size:11
}
   
.th_table2 
{ 
	border:1px solid #ADC3D5;
	text-align:right;
}
   
   
table.menu
{
   border:1px solid black;
   background-color:white;
   width:110;
   height:40;
   padding:5px;
}

td.menu
{
   background-color:white;
   width:110;
   height:25;
   padding:2px;
   z-index:7000;
}

.point_menu
{
   background-position:left;
   font-size:12px;
   background-repeat: no-repeat;
   padding:5px 10px 5px 20px;
   text-decoration: none;
   color:black;
   position:relative;
}

td.menu:hover
{
		background-color: #f1f4f9;
	 box-shadow:inset 0px 0px 4px 0px white;
    -webkit-box-shadow:inset 0px 0px 4px 0px white;
    -moz-box-shadow:inset 0px 0px 4px 0px white;
}

a.menu
{
   background-image: url(/bitrix/images/fileman/view.gif);
} 

a.menu2
{
   background-image: url(/bitrix/images/fileman/edit_text.gif);
} 

a.menu_del
{
   background-image: url(/bitrix/images/fileman/htmledit2/c2del.gif);
} 
   
a.menu_unzip
{
	background-image: url(/bitrix/images/fileman/htmledit2/redo.gif);
}  
  
a.menu_dw
{
   background-image: url(/bitrix/images/fileman/types/file.gif);
} 
   
   
   A {
   text-decoration: none;
   color:#36648B;
   } 

</style>
<script>
var dragObjects=new Array('log3','list','load','param','stepdiag','main_info','test_window');
<?=$win;?>
</script>
</head>
<body class="body" style='overflow:hidden;' onmousedown="Hide(event)" onkeydown="ShowFileMan(event)">
<div id="custom_windows">

<?
ob_start();
?>
<table class="file_panel" width=100%>
<tr><td width=5% align=right>
Фильтр:<br>
Путь:<br>
</td>
<td >
<input id=search_str style='font-size:10px;width:90%;margin-bottom:2px;'  name='search_str' OnChange=GetFileList('path_fileman','testfileman') value='<?if(isset($_SESSION['bx_1c_import']['filter'])) echo $_SESSION['bx_1c_import']['filter'].'\'>'; else echo '\'>'?><br>
<input onmousedown="moveState = false;" onmousemove="moveState = false;" OnChange="GetFileList('path_fileman','testfileman');" id="path_fileman" style="font-size:10px;width:90%;" name="path_fileman" value='<?if(isset($_SESSION['bx_1c_import']['path'])) echo $_SESSION['bx_1c_import']['path']; else echo $UPLOAD_DIR.'/1c_catalog/';?>'>
</td>
</tr>
<tr><td colspan=2 align=left>
<?=AddButton('go');?>
</td></tr>
</td></tr>
</table>

<?
$beforeInner=ob_get_contents();
ob_end_clean();
$afterinner='<hr><div id="info">----</div>';

$inner='<table cellspacing="0" cellpadding="0" style="width:100%;font-size:10px;"><tr><td>
<div id="testfileman" class="file_panel"></div>
<td class="file_panel" valign=top width=100>
<input type=checkbox id=isdir>папку<br>
<input  id=cfilename style="font-size:11px; onmousedown="moveState = false;" onmousemove="moveState = false;" value=\'bx_test.php\'>'.AddButton('crfile',false,true).'<hr>'.AddButton('upfile',false,true).'</td>
</tr>
</table>';

AddWindow("test_window","Файловая структура",'testsfileman',$inner,false,"",false,$beforeInner,$afterinner);
AddWindow("upload_file","Загрузка файла файл",'upload_file_id','<iframe id="file_panel"  height=150 src="'.$script_name.'?upload=Y"></iframe>',$DefaultWinStyleSmall);
?>
		
</div>


			
<div id='ind_'style="width:300px;display:none;left:360px;padding:1px;z-index:10000;position:absolute;background-color:#EEE8CD;border:1px solid grey;height:30px;cursor:move;">
<div>Прогресс выполнения шага импорта...</div>
<div id='indicate' style="width:0;background-color:green;border:none;z-index:1;height:10;text-align:center;"></div>
</div><br>
<div id="main_info" class="divwin_info round_win">
<b>Поиск</b><hr>
<div class="closeButton" onclick="Close('main_info')">X</div> 
<table>
<tr>
	<td valign="top">
		<table style='width:310px;font-size:11px;border:1px solid #ADC3D5;background-color:white;'>
					<tr>
						<td align="center">
<p >Для поиска выделенного в тексте XML_ID нажмите <em>alt+s</em><br></p>
										<input style="font-size:10px" size=45 id="q" type="text" name="search" value="XML_ID"><br>
										<?AddButton('searchbutton');?>									
						</td>
					</tr>
					<tr>
						<td align="center">
							<div id="result"></div>
						</td>
					</tr>
				</table>		
	</td>
						</tr>
</table>
	</td>
</tr>
</table>
</div>

<div class="divwin round_win" id="log3"> 
<b>Лог импорта файла</b>
<hr>
<div class="closeButton" onclick="winClose()">Х</div> 
<div id="log" style='font-size:10px;padding:3px;background: white;overflow-y:scroll;height:300'></div>
<div id="timer" style='font-size:12px;padding:5px;background: white;'></div>
</div>	

<?if (!isset($_REQUEST['check'])):?>
<title>Интеграция с 1С</title>
<?if (@$_POST['AJAX']!='Y'){?>
<div id="main" class="divwin_times round_win" onselectstart="return false" onmousedown = "initMove(this, event)";   onmouseup = "moveState = false;"  onmousemove = "moveHandler(this, event);">
<b>Смена метки времени</b><hr>
<div class="closeButton" onclick="Close('main')">X</div> 
<div style='font-size:11;padding:3;background: white;'>
<table align='center'>
<tr>
<th class="th_table">Путь</th>
<th class="th_table2"><input onmousedown="moveState = false;" onmousemove="moveState = false;" id='path1' type="text" size="30" value="<?if(isset($_POST['path1'])) echo $_POST['path1']; else echo $catalog_import_path;?>" name="path1"></th>
</tr>
<tr>
<th class="th_table">Дата </th>
<th class="th_table2"><input onmousedown="moveState = false;" onmousemove="moveState = false;" id='date_e' type="text" size="30" value="<?if(!$_POST['date']=='') echo $_POST['date']; else echo $date;?>" name="date_e"></th>
</tr>
<tr><td COLSPAN=2 align="center">
<?AddButton('change1');?>
</td></tr>
</table>
<?}?>
<?
if (file_exists("bx_exchange_date.log"))
{
$f = fopen("bx_exchange_date.log", 'r');
$real_date=fread($f, filesize("bx_exchange_date.log"));
fclose($f);
if (@$_POST['AJAX']=='Y') {echo $real_date;die();}
echo "<hr>Реальная дата последнего обмена: ". $real_date;
}
?>
</div>
</div>
<div id="list" class="divwin_orderlist round_win" onselectstart="return false" onmousedown = "initMove(this, event)";   onmouseup = "moveState = false;"  onmousemove = "moveHandler(this, event);">
<b>Изменения в заказах</b><hr><div class="closeButton" onclick="CloseOrderList()">Х</div></div>
<div id="param" class="divwin_param round_win" onselectstart="return false" >
<div class="closeButton" onclick="Close('param')">X</div> 
<b>Параметры выгрузки заказов</b><hr>
<div style="background-color:white;border-top:1px solid #ADC3D5;text-align:center;font-size:11px">
<table align="center">
<tr>	
<th class="th_table">Путь</th>
<th class="th_table2"><input id="path" type="text" size="25" value="<?if(isset($_POST['path'])) echo $_POST['path']; else echo $catalog_import_path;?>" name="path"></th>
</tr>
<tr><th class="th_table">Оплаченные</th>
<th style="text-align:left;border:1px solid #ADC3D5;">
<input id="PAYED" type="checkbox" <?if(isset($_POST['PAYED'])) echo "checked";?> value='Y' name="PAYED"></th></tr>  
<tr><th class="th_table">Доставленные</th>
<th style="text-align:left;border:1px solid #ADC3D5;">
<input onmousedown="moveState = false;" onmousemove="moveState = false;" id="DELIVERY" type="checkbox" <?if (isset($_POST['ALLOW_DELIVERY'])) echo "checked";?> value='Y' name="ALLOW_DELIVERY"></th></tr>
<tr><td COLSPAN=2 align="center">
<input type="button" class="small_but" OnClick="GetOrders()" value="Проверить"></td></tr>
</table>
</div></div>

<div id="stepdiag" class="divwin_stepdiag round_win">
<div class="closeButton" onclick="Close('stepdiag')" >X</div> 
<b>Пошаговый импорт</b><hr>
<div style="background-color:#E8E8E8;border-top:1px solid #ADC3D5;text-align:center;font-size:11px;width:100%">
<div class="small_but" OnMouseOver="LightOn(this,'Удаление врем. таблиц')" OnMouseOut="LightOff()" OnClick="StartStep(0)">Удаление врем. таблиц</div>
<div class="small_but" OnMouseOver="LightOn(this,'Удаление врем. таблиц')" OnMouseOut="LightOff()" OnClick="StartStep(1)">Создание врем. таблиц</div>
<div class="small_but" OnMouseOver="LightOn(this,'Импорт во врем. талицу')" OnMouseOut="LightOff()" OnClick="StartStep(2)">Импорт во врем. таблицу</div>
<div class="small_but" OnMouseOver="LightOn(this,'Создание индекса')" OnMouseOut="LightOff()" OnClick="StartStep(3)">Создание индекса</div>
<div class="small_but" OnMouseOver="LightOn(this,'Импорт метаданных')" OnMouseOut="LightOff()" OnClick="StartStep(4)">Импорт метаданных</div>
<div class="small_but" OnMouseOver="LightOn(this,'Импорт секций')" OnMouseOut="LightOff()" OnClick="StartStep(5)">Импорт секций</div>
<div class="small_but" OnMouseOver="LightOn(this,'Удаление секций')" OnMouseOut="LightOff()" OnClick="StartStep(6)">Удаление секций</div>
<div class="small_but" OnMouseOver="LightOn(this,'Обработка элементов')" OnMouseOut="LightOff()" OnClick="StartStep(7)">Обработка элементов</div>
<div class="small_but" OnMouseOver="LightOn(this,'Удаление элементов')" OnMouseOut="LightOff()" OnClick="StartStep(8)">Удаление элементов</div>

</div></div>

<?endif;?>		
		<div id='para1' style='height:80%;top:5px;left:10px;postition:fixed;'>
		<table cellspacing='0' cellpadding='0' width='100%' height='100%' >
		<tr>
			<td id='tab_zone' valign=top height='25px'>
			</td>
			<td>
			<?=AddButton('test_123',false,true);?>
			</td>
		</tr>
		<tr>
			<td id='field_zone' valign=top >
			</td>
						<td width='26%' align=left valign=top>
						<div id='main_menu_panel' class='left_panel'>
						
<?
AddButton('delete');
AddButton('refresh');
echo '<hr><div  style="padding:4px;text-align:center;background:#d8dcf0">Основное меню</div>';
foreach($MenuArray as $key=>$value)
AddButton($key,true);

?>
<hr>
<div style="padding:4px;text-align:center;background:#d8dcf0">Заказы</div>
<div class="small_but" align=center OnClick="SaveMe('<?=$host;?>')"> XML-файл заказов</div>

   <div class="small_but" align=center onclick="javascript:_BlankXML('<?='view-source:'.$host.'?mode=query'?>')" OnMouseOver="LightOn(this,'будет открыто <b>отдельное</b> окно с текстом xml-файла заказов, которые отдаст сайт 1с-ке при следующем обмене заказаим с 1С')" OnMouseOut="LightOff()">XML в отдельном окне</div>
</div>
<br clear="both" />
<br>
<?if (file_exists($_SERVER["DOCUMENT_ROOT"]."/import_element_log.txt")) $display='block';
else $display='none';?>

<div id="element_log" class='left_panel' style="display:<?=$display?>"><a href='javascript:ShowFile("import_element_log.txt","/","N")'>посмотреть лог</a><span onclick='javascript:Delete("import_element_log.txt","","/");this.parentNode.style.display="none";'> X</span></div>
			</td>
</tr>
	<tr>
			<td>
		<div class='ver'><?echo '1C Diag ver. '.ver;?></div>
			</td>
		</tr>
		</table>
		</div>

<table id="tbl" cellpadding=4 cellspacing=0 style="position:relative;width:70%;z-index:1;left:350;text-align:left;font-size:10pt;">
		<tr><td style='padding-top:45px;text-align:right;'>
		</td>
		<td style='width:100%;'>
		
		</td></tr>
</table>		
<div id="load" align="right" style='border:1px solid black;width:200px;z-index:10000;font-size:15px;position:fixed;top:85%;background-color:white;display:none;'>
Загрузка...
</div>


<div id="menu_1" style="z-index:7000;display:none;">
<?BuildContextMenu();?>
</div>
<table id='ext_import' width='100%'>
	<tr>
		<td valign="top" id="ext_log" style="position:relative;background:white;width:50%;padding:5px">
		</td>
		<td class="auth_form" valign="top">
		<h3  style="padding:4px;text-align:center;background:#d8dcf0">Данные для авторизации</h3>
				Путь<br/>
				<input class="auth_field" id="ext_path" name="current_path" value="http://s150.office.bitrix.ru:8888/bitrix/admin/1c_exchange.php"><br/>				
				Имя файла<br/>
				<input  class="auth_field"id="ext_filename"  name="ext_filename" value="import.xml"><br/>
				Логин<br/>
				<input class="auth_field" id="login" name="login" value="admin"><br/>
				<input id="phpsessid" type="hidden" value="">
				Пароль<br/>
				<input class="auth_field" id="pass" name="pass" type="password" value="123456"><br/>
				<input class='small_but' type='button' name="start_ext_import" value="начать" onclick="ext_start()"><hr/><h3  style="padding:4px;text-align:center;background:#d8dcf0">Заголовки ответа</h3>
			<div id="ext_headers_log" style="height:200px;overflow:auto;padding:3px;">
			</div>	
		</td>
	</tr>
</table>

<?

    $inner="<div class='import_form' id='ipfs' style='padding:5px'>";
    $inner.='<a href="javascript:OpenWin(\'/bitrix/admin/1c_admin.php?lang=ru\')">Настройки интерации</a><hr>';
    $inner.='<input alt=\'Импорт осуществляется стандартным компонентом + все изменения и добавления товаров пишуться в лог\' type=\'checkbox\' id=\'impself\'><span style="font-size:10px" >Импортировать этим скриптом</span><br>';
    $inner.='<input  class=small_but type="button" id="cstart" onclick="CatalogImport()" value="импорт по списку"><br><br>';
    ob_start();
    ShowFileSelect('cat_file','Файл каталога в '.$UPLOAD_DIR.'/1c_catalog/:',$UPLOAD_DIR.'/1c_catalog/','xml',2,'ConfirmImport(\'import.xml\')');
    //ShowFileSelect('off_file','Файл предложений в /upload/1c_catalog/:','/upload/1c_catalog/','xml',2,'start(\'offers.xml\')');
    ShowFileSelect('order_file','Файл заказов в '.$UPLOAD_DIR.'/1c_exchange/:',$UPLOAD_DIR.'/1c_exchange/','xml',2,'OrderImport(\'hz\')');
    ShowFileSelect('worker','Файл сотрудников в '.$UPLOAD_DIR.'/1c_intranet/:',$UPLOAD_DIR.'/1c_intranet/','xml',2,'ConfirmImport(\'company.xml\')');
	 
    $inner.=ob_get_contents();
     ob_end_clean();
	 $inner.="</div>";
	 echo $inner;
?>
</body>
<div id="mess_decorate" style='width:100%;padding:5;z-index:10000;opacity:0.7;align:right;font-size:14;position:fixed;top:92%;background-color:#FFE4B5;display:none;'>
Что сейчас произойдёт:
</div>
<div id="text_mess" style='width:100%;padding:5;left:200;z-index:10000;align:right;font-size:14;position:fixed;top:92%;display:none;'>
пока ничего
</div>
</html>

<script>

var i,status,des,a
var log=BX("log");
var fileinfo=BX("info");
var result=BX("result");
var timer=BX("timer");
var load=BX("load");
var zup_import=false;
var text_mess=BX('text_mess');
var mess_decorate=BX('mess_decorate');
var load=BX("load");
globalpath='<?=$_SESSION['bx_1c_import']['path']?>';
var ImportStep=0;
var mywindows=new Array("log3","main","list","main_info","bx_main_menu","stepdiag","param");
if (!new_id)
var new_id=new Array();
var moveState = false;
var x0, y0;
var divX0, divY0;
var lastwin="main_info";
var i=1;
var status="continue";
var menu=BX("menu_1");
var NewFieldID=1;
var numfile=0;
var filecount=0;
var circule=false;

			
function CreateFileDialog(Name,where)
{
	var where='testfileman';
	var newP=document.createElement('input');
	//var newP=document.createElement('div');
	var newField=document.createElement('div');
	var FieldID=NewFieldID+'_field';
	var TabID=NewFieldID+'_tab';
		NewFieldID++;
		//alert(TabID);
		//создаём поле для таба
		newField.style.width='350px';
		newField.style.height='80px';
		newField.style.padding='5px';
		newField.style.background = '#FFF8DC';
		newField.style.position='absolute';
		newField.style.top='250px';
		newField.style.left='130px';
		newField.style.display='block';
		newField.style.border='1px solid #00C5CD';
		newField.style.zIndex='99';
		newField.innerHTML='<input type=checkbox id=isdir>Создаём папку, а не файл<br><br>';
		newField.innerHTML+='Имя файла/папки:<br>'+'<input id=cfilename value=\'bx_test.php\'size=40><input type=button value=\'создать\' onclick=CreateFile(\'cfilename\',\'path_fileman\',\'testfileman\')>';
		BX(where).appendChild(newField);
		
		return newField.id;
}

function CreateIBlock()
{
	var	iblock1c=BX('1ciblock'); 
	var	iblocktype=BX('iblocktype'); 
	q="<?=$script_name?>?action=createiblocktype&iblocktype="+iblocktype.value;
	if (iblock1c.value=='on')
	q=q+'&USE_IBLOCK_TYPE=Y';
	AjaxRequest(q,'successiblock',false);
}
				
function AddWindowRequest(url,id,windowid)
{
	if ((("#" + mywindows.join("#,#") + "#").search("#"+windowid+"#") != -1)||(("#" + new_id.join("#,#") + "#").search("#"+windowid+"#") != -1))
	{
		BX(windowid).style.display="block";
	}
	else 
	{
		AjaxRequest(url,id,true);
		new_id[new_id.length]=windowid;
		dragObjects[dragObjects.length]=windowid;
		
	}
}

function AjaxRequest(url,id,AddResult)
{
	var ajaxreq=createHttpRequest();
	load.style.display="block";
	load.innerHTML=' <img align="center" src="http://vkontakte.ru/images/upload.gif" width="50"/> загрузка...';
	var callback=function(ajaxreq)
	{
		if (ajaxreq.readyState == 4)
		{		
			if (AddResult==false)
			{			
				BX(id).innerHTML=ajaxreq.responseText;
			}
			else 
			{
				BX(id).innerHTML+=ajaxreq.responseText;
			}
		}
		InitMoveableObjects();
	}

	AjaxGet(url,callback)
	
}

function Download(file,path)
{
	JustHide();
	BX("dwframe").src="<?=$script_name?>?action=download&file="+file+"&path="+path;
}

// создание объекта XMLHttpRequest
function createHttpRequest() 

   {
	var httpRequest;
		if (window.XMLHttpRequest) 
		httpRequest = new XMLHttpRequest();  
		else if (window.ActiveXObject) {    
		try {
		httpRequest = new ActiveXObject('Msxml2.XMLHTTP');  
		} catch (e){}                                   
		try {                                           
		httpRequest = new ActiveXObject('Microsoft.XMLHTTP');
		} catch (e){}
		}
	return httpRequest;

}
	var	edit=BX('e'); 
	//var	editutf=BX('eutf'); 
	var	view=BX('v'); 
	var	viewutf=BX('vu'); 
	var	del=BX('d'); 
	var	unzip=BX('u'); 
	var	down=BX('dw'); 
// показываем недоменю
function ShowMenu(event)
{
	<?=$mainmenu;?>
	var evt=fixEvent(event);
	var ext;
	ext=evt.target.textContent.substr(evt.target.textContent.length-4,evt.target.textContent.length);
	menu.style.display="block";
	menu.style.zIndex=10000;
	menu.style.top=evt.clientY+'px';
	menu.style.left=evt.clientX+'px';
	menu.style.position="absolute";
	view.href=evt.target.href;
	viewutf.href="javascript:ShowFile('"+evt.target.textContent+"','"+globalpath+"','Y')";
	//editutf.href="javascript:Showforedit('"+evt.target.textContent+"','"+globalpath+"','Y')";
	edit.href="javascript:Showforedit('"+evt.target.textContent+"','"+globalpath+"','N')";
	del.href="javascript:Delete('"+evt.target.textContent +"','"+evt.target.parentNode.parentNode.parentNode.parentNode.parentNode.id+"')";
	down.href="javascript:Download('"+evt.target.textContent+"','"+globalpath+"')";

	if(ext=='.zip')
	{
		BX("unzip_").style.display='block';
		unzip.href="javascript:UnZip('"+evt.target.textContent+"','"+evt.target.parentNode.parentNode.parentNode.parentNode.parentNode.id+"')";
	} else BX("unzip_").style.display='none';

	   return false;
}

//функция запускает процесс импорта
function CStart()
{
	var path;
	var filecount=BX('cat_file').options.length;	
	if (filecount>1)
			circule=true;
		numfile=1;

	CatalogImport();
}

		
function CatalogImport()
{
	var file=BX('cat_file').options[BX('cat_file').selectedIndex].innerHTML;
	if(!file)
		alert('Не указан файл!');
	path='<?=$script_name?>';
	if(BX('impself').checked==false)
		path='<?=$catalog_import_path?>';
	url=path+"?type=catalog&mode=import&filename="+file;
	if (!BX('log2'))
	{
		var log2=document.createElement('DIV');
		log2.id='log2';
		log2.style.fontSize='15px';
		log2.style.padding='3px';
		log2.style.background='white';
		log2.style.height='95%';
		log2.style.overflowY='scroll';
		BX("tab1_field").appendChild(log2);
	}
	log=BX("log2");
	log.innerHTML="<b>Импорт "+file+"</b><hr>";
	load.innerHTML='идёт загрузка...<img align="center" src="http://gifanimation.ru/images/ludi/17_3.gif" width="30"/>';
	query_1c(url);
}	

function UserImport(file)
{
	
	if(!file)
		alert('Не указан файл!');
		
	BX("tab1_field").innerHTML='<div id="log2" style="font-size:15px;padding:3px;background: white;overflow-y:scroll;height:88%"></div>'
	log=BX("log2");
	log.innerHTML="<b>Импорт "+file+"</b><hr>";
	load.innerHTML='идёт загрузка...<img align="center" src="http://gifanimation.ru/images/ludi/17_3.gif" width="30"/>';
	path='<?=$$user_import_path?>';
	url=path+"?type=catalog&mode=import&filename="+file;
	query_1c(url);
}	


		
function AjaxPost(url,data,callback)
{
	var obj=createHttpRequest();
	load.style.display="block";
	obj.open("POST", url, true);
	obj.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	obj.onreadystatechange=function() {callback(obj);};
	obj.send(data);
}

function AjaxGet(url,callback)
{
	var obj=createHttpRequest();
	load.style.display="block";
	load.style.innerHTML='<img align="center" src="http://vkontakte.ru/images/upload.gif" width="50"/>';
	obj.open("GET", url, true);
	obj.onreadystatechange=function() 
	{
		callback(obj);
		if (obj.readyState == 4)
			load.style.display="none";
	};
	obj.send(null);
}

function ext_start()
{
	load.innerHTML='Тихо, идёт импорт... <img align="center" src="http://gifanimation.ru/images/ludi/17_3.gif" width="30"/>';
	var login=BX('login').value;
	var filename=BX('ext_filename').value;
	var pass=BX('pass').value;
	var url=BX('ext_path').value;
	var phpsessid=BX('phpsessid').value;
	urldata='{"login":'+login+',"filename":'+filename+',"pass":'+pass+',"url":'+url+',"phpsessid":'+phpsessid+'}';
	AjaxPost("<?=$script_name?>","data="+urldata+"&mode=exchange",ExtImportCallBack);
}
		

function ExtImportCallBack(ajaxreq)
{

	if (ajaxreq.readyState == 4)
	{	
		try
		{
			json_data=eval("(" +ajaxreq.responseText+")");
			if (json_data.phpsessid && json_data.status=='success')
			{
				BX('phpsessid').value=json_data.phpsessid;
				ext_start();
			}
			else
			{
				
				BX('ext_log').innerHTML+=json_data.text+'<br>';
				if (json_data.status && json_data.status=='progress')
					ext_start();
				else
				{
					bxtabs.AlertActiveTab('tab3');
				}
			}
			BX('ext_headers_log').innerHTML+=json_data.headers+'<hr>';
		}
		catch(err)
		{
			bxtabs.AlertActiveTab('tab3');
			load.style.display="none";
			BX('ext_log').innerHTML+="Ошибочный ответ сервера:<br>"+ajaxreq.responseText+"<br>";
		}
		
	}

}		
//функция осущетсвляет импорт из файла
function query_1c(url)
		{
		sInd=0;
		BX('indicate').style.width=0;
		var import_1c=createHttpRequest();
		var getstep=createHttpRequest();
		gs="<?=$script_name?>?action=getstep";
		getstep.open('GET',gs,true);
		
		getstep.onreadystatechange = function() 
			{
				if (getstep.readyState == 4)
				{
					ImportStep=getstep.responseText;
					r=url+"&step="+ImportStep;
				//alert(r);
					load.style.display="block";
					import_1c.open("GET", r, true);
				import_1c.onreadystatechange = function() 
				{
				a=log.innerHTML;
				if (import_1c.readyState == 4 && import_1c.status == 0)
						{
						error_text="<em>Ошибка в процессе выгрузки</em><div style='width:270px;font-size:11px;border:1px solid 				black;background-color:#ADC3D5;padding:5px'>Сервер упал и не вернул заголовков.</div>"
							log.innerHTML=a+"Шаг "+i+": "+error_text;
							load.style.display="none";
							status="continue"
							alert("Import is crashed!");
						}
				
				if (import_1c.readyState == 4 && import_1c.status == 200)  
							{
							if ((import_1c.responseText.substr(0,8)!="progress")&&(import_1c.responseText.substr(0,7)!="success"))
							{
								if (import_1c.responseText.substr(0,7)=="failure")
								check_file(file);
							
								error_text="<em>Ошибка в процессе выгрузки</em><div style='font-size:11px;border:1px solid black;background-color:#ADC3D5;padding:5px'>"+import_1c.responseText+"</div>"
								log.innerHTML=a+"Шаг "+i+": "+error_text;
								status="error"
								circul=false;
							}
							else
							{
								n=import_1c.responseText.lastIndexOf('s')+1;
								l=import_1c.responseText.length;
								mess=import_1c.responseText.substr(n,l);
								log.innerHTML=a+"Шаг "+i+": "+mess+" ("+seconds+" сек.)"+"<br>";
								BX('ind_').style.display='none';
								seconds=0;
								i++;
							}

							if ((import_1c.responseText.substr(0,7)=="success")||(status=="error"))
							{
							//alert(BX('cat_file').options.length);
							//alert(numfile);
								load.style.display="none";
								load.innerHTML=' <img align="center" src="http://vkontakte.ru/images/upload.gif" width="50"/> загрузка...';
								BX('ind_').style.display='none';
								status="continue"
								proccess="N";
								timer.innerHTML="<hr>Время выгрузки: <b>"+minute+" мин. "+m_second+" сек.</b>";
								//alert(BX('cat_file').options[numfile].text)
								if (circule==true && numfile<=BX('cat_file').options.length-1)
								{
									log.innerHTML+='<br><b>Импорт '+BX('cat_file').options[numfile].text+'</b><hr>';
									query_1c(BX('cat_file').options[numfile].text);
									numfile++;
								}
								else
								{
									numfile=0;
									circule=false;
								}
								bxtabs.AlertActiveTab('tab1');

						if(BX('impself').checked!=false)
							BX('element_log').style.display="block";
							}
							else 
							{
								query_1c(url);
							}
				} 
						
						

				}; 
import_1c.send(null);
	//alert(ImportStep);
				}
			}
		getstep.send(null);	
		}

		
function OrderImport(elem)
{
	var	file=BX('order_file').options[BX('order_file').selectedIndex].innerHTML;
	if (!BX('log2'))
	{
		var log2=document.createElement('DIV');
		log2.id='log2';
		log2.style.fontSize='15px';
		log2.style.padding='3px';
		log2.style.background='white';
		log2.style.height='95%';
		log2.style.overflowY='scroll';
		BX("tab1_field").appendChild(log2);
	}
	var log=BX('log2');
	StartTime();
	var callback= function(ajaxreq) 
			{
			if (ajaxreq.readyState == 4)  
							{
				                log.innerHTML=ajaxreq.responseText;
								proccess='N';
								alert('Длительность импорта заказов: '+seconds+' сек.');
								bxtabs.AlertActiveTab('tab1');								
							}
							
			}
	AjaxGet("<?=$catalog_import_path?>?type=sale&mode=file&filename="+file,callback)
}
		
//проверка, существует ли файл, права на него
function check_file(file)
{
	var callback= function(ajaxreq) 
	{				
		if (ajaxreq.readyState == 4 && ajaxreq.status == 200)  
		log.innerHTML=log.innerHTML+ajaxreq.responseText;
	};
	AjaxGet("<?=$script_name?>?check_file=Y&file="+file,callback)
			
}
				
				
function StartStep(numstep)
{
	var stepfile=BX("stepfile").value;
	var callback= function(ajaxreq) 
	{
		if (ajaxreq.readyState == 4)
			CatalogImport();
	}

	AjaxGet("<?=$script_name?>?setstep="+numstep,callback)
}
	
	//отображаем  информацию по товарам, группам и предложениям 		
function ShowInfo(file)
{
	var fileinfo=BX("info");
	fileinfo.style.opacity=0.4;
	var callback = function(ajaxreq) 
			{
			
				if (ajaxreq.readyState == 4 && ajaxreq.status == 200)  
					{

						fileinfo.innerHTML=ajaxreq.responseText;
						fileinfo.style.opacity="";
					}
			};
	AjaxGet("<?=$script_name?>?info=Y&file="+file,callback);	
}
				
				
				
	//сбрасываем шаг импорта		
function reset()
{
	var callback=function(ajaxreq)
	{
		if (ajaxreq.readyState == 4 && ajaxreq.status == 200)  
		alert("Шаг импорта обнулён!");
	}
	AjaxGet("<?=$script_name?>",callback);
	
}
				
				
				
				
	//удаляем скрипт
function delete_file()
	{
		if (confirm('Удалить файл?'))
			//edirect("bx_1c_import.php?delete=Y");
			document.location = "<?=$script_name?>?delete=Y";
	}

function ConfirmImport(file)
	{
		if (confirm('Импортировать файл?'))
			CatalogImport();
	}
	
	
	//ищем товар по xml_id
function searchbyxmlid()
{
	var qs=BX("q");
	var result=BX("result");
	result.innerHTML=' <img align="center" src="http://vkontakte.ru/images/upload.gif" width="50"/> ';
	var callback = function(ajaxreq) 
	{				
		if (ajaxreq.readyState == 4 && ajaxreq.status == 200)  
				result.innerHTML=ajaxreq.responseText;
	};
		
		AjaxGet("<?=$script_name?>?search="+qs.value,callback);
}
					
						
var oldelem,oldop,borderold//переменные цвета



//подцветка кнопки или ещё чего нибудь
function LightOn(el,message)
{
oldelem=el;
el.style.cursor = 'hand'; 
if (message!='') 
{
//mess_decorate.style.display='block';
//text_mess.style.display='block';
//text_mess.innerHTML=message;
}
//lert(elem);
}



//возвращаение цвета кнопки или ещё чего-нибудь
function LightOff()
{
	var el= oldelem;
}		

var Linkoldelem,Linkoldop,Linkborderold//переменные цвета



//подцветка ссылки
function LinkLightOn(elem,lcolor)
{
	var el=BX(elem);
	Linkoldelem=elem;
	el.style.cursor = 'hand'; 
	Linkborderold=el.style.color;
	el.style.color=lcolor;

}

function OpenWin(path)
{
	window.open(path,'new','width=1000,height=700, top=100, left=200,toolbar=1 scrollbars=yes');
}

//возвращаение цвета ссылки 
function LinkLightOff()
{
	var el= BX(Linkoldelem);
	el.style.color=Linkborderold;
}		

// задаём переменные таймера процесса импорта
var m_second=0
var seconds=0
var minute=0
var proccess="Y"
var sInd=0;
var sIntStep=<?=IntVal(300/$interval);?>

//собственно таймер
		function display()
		{
		var indicate=BX('indicate');
			if (m_second==60)
			{
			m_second=0;
			minute+=1;
			}
			if (proccess=="Y")
			{
			seconds+=1;
			m_second+=1;
			//alert(ImportStep);
			if ((ImportStep=='2')||(ImportStep=='7'))
			{
			BX('ind_').style.display='block';
					if (sInd<300)
					{
						sInd=sInd+sIntStep;
						indicate.style.width=sInd +'px';
					} else {sInd=0;}
			}
			else
			{
					sInd=0;
					indicate.style.width=0;
					BX('ind_').style.display='none';
			}
			setTimeout("display()",1000);			
		}
		}	


		function gotime()
		{
			if (proccess=="Y")
			{
			seconds+=1;
			setTimeout("gotime()",1000);			
			}
		}	

	function StartTime()
		{
			proccess="Y";
			seconds=0;
			gotime();
		}			
	
	
//окна дивные
var sStep = 16; 
var sTimeout = 15;
var sLeft = 160;
var sRight = 160;
var wObj;

//закрываем окно
function Close(param)
{
BX(param).style.display='none';
}
function winOpen() 
{
	wObj.style.display = 'block'; 
	if (sLeft > 0) {
		sRight += sStep; 
		sLeft -= sStep; 		
		var rect = 'rect(auto, '+ sRight +'px, auto, '+ sLeft +'px)';		
		wObj.style.clip = rect;		
		setTimeout(winOpen, sTimeout); 
	}
}


//закрывем окно красиво
function winClose() 
{
	if (sLeft < sRight) 
	{
		sRight-=sStep; 
		sLeft+= sStep; 
		var rect ='rect(auto, '+ sRight +'px, auto, '+ sLeft +'px)';
		wObj.style.clip = rect;
		setTimeout(winClose, sTimeout);
	}
	else wObj.style.display = 'none';
}

var cur="";
var d=document
var wincolor
var winopacity
var oldindex=false;
var lastwin=false;

//двигаем  div'ные окна
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////Заказы и XML///////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////

function Showforedit(file,path,is_utf)
{
	JustHide();
    var elem = BX("tab0_field");
    var tb=BX("tbl");
    var callback = function(ajaxreq) 
    {
        if (ajaxreq.readyState == 4)  
        {
            if (ajaxreq.responseText=='')
            {
                    elem.innerHTML='Файл отсутстует. Произведите выгрузку из 1С.';
                    tb.style.display="block";
            }
            else
            {
                    elem.innerHTML=ajaxreq.responseText;
					bxtabs.AlertActiveTab('tab0');
            }
        }       
    }; 
    AjaxGet("<?=$script_name?>?mode=edit&file="+file+"&path="+path+"&isutf="+is_utf,callback)
}

function SaveFile(file)
{
	var text = encodeURIComponent(BX("textfile").value);
	var sfstatus=BX("sfstatus");
	var save=createHttpRequest();
	load.style.display="block";
	sfstatus.style.display='none';
	save.open("POST", "<?=$script_name?>", true);
	save.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	save.setRequestHeader("Content-length", text.length);


	save.onreadystatechange = function() 
	{
				if (save.readyState == 4)
				{
				//alert(save.responseText);
				if (save.responseText=='OK')
					sfstatus.innerHTML="<b>Изменения в файле сохранены<b>"
					//sfstatus.innerHTML=save.responseText;
					else 
					sfstatus.innerHTML="<b style='color:red'>Ошибка при сохранении файла</b>";
					//sfstatus.innerHTML=save.responseText;
					sfstatus.style.display='block';
					load.style.display="none";
				}       
			}; 
	save.send("action=save&filename="+file+"&text="+text);
	}

	function ChangeLastMoment()
	{
	var path1=BX("path1").value;
	var date=BX("date_e").value;
	alert(BX("date_e").value);
	var clastmoment=createHttpRequest();
	load.style.display="block";
	clastmoment.open("POST", "<?=$script_name?>", true);
	clastmoment.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	clastmoment.onreadystatechange = function() 
	{
				if (clastmoment.readyState == 4)
				{
					alert('Теперь дата последнего обмена: '+clastmoment.responseText);
					load.style.display="none";
				}       
			}; 
	clastmoment.send("path1="+path1+"&date="+date+"&change=Y&AJAX=Y");
}

function Delete(file,workarea,full)
{
	var del=createHttpRequest();
	menu.style.display="none";
	if (confirm('Удалить '+file+'?'))
	{
	load.style.display="block";
	del.open("POST", "<?=$script_name?>", true);
	del.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	del.onreadystatechange = function() 
	{
				if (del.readyState == 4)
				{
					if (del.responseText!='success') 
						alert("Ошибка удаления файла");
					GetFileList2(globalpath,workarea);
					load.style.display="none";
				}       
			};
	q="action=deletefile&filename="+file;
	if (full)
		q=q+"&fullpath="+full;		
	del.send(q);
	}
}

function DeleteLog(file,workarea,full)
{
	var del=createHttpRequest();
	menu.style.display="none";
	if (confirm('Удалить '+file+'?'))
	{
	load.style.display="block";
	del.open("POST", "<?=$script_name?>", true);
	del.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	del.onreadystatechange = function() 
	{
				if (del.readyState == 4)
				{
					if (del.responseText!='success') 
						alert("Ошибка удаления файла");
					GetFileList2(globalpath,workarea);
				}       
			};
	q="action=deletefile&filename="+file;
	if (full)
		q=q+"&fullpath="+full;		
	del.send(q);
	}
}

function UnZip(file,workarea)
{
JustHide();
var unzipfile=createHttpRequest();
if (confirm('Распаковать '+file+'?'))
{
menu.style.display="none";
load.style.display="block";
unzipfile.open("POST", "<?=$script_name?>", true);
unzipfile.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
unzipfile.onreadystatechange = function() 
{
			if (unzipfile.readyState == 4)
			{
			if (unzipfile.responseText!='1') alert("Ошибка распаковки файла"); 
			GetFileList2(globalpath,workarea);
			}       
		};
		
unzipfile.send("action=unzip&filename=<?=$_SERVER['DOCUMENT_ROOT']?>"+globalpath+file);
}
}

function ShowHideSection(elem)
		{
			var t='block';
			if(BX(elem).style.display=='block')
			{t='none';}
			BX(elem).style.display=t;
		}

//показываем файлы импорта
function ShowFile(file,path,is_utf,workid)
{
JustHide();
if (!workid)
{
var elem = BX("tab0_field");
} else {var elem = BX(workid);}
var tb=BX("tbl");

if (file=="import")	{file=BX("cat_file").value;}
if (file=="offers") {file=BX("off_file").value;}

var callback= function(ajaxreq) 
{
	if (ajaxreq.readyState == 4)  
	{
	if (ajaxreq.responseText=='')
		{
		elem.innerHTML='Файл отсутстует или он пустой.';
		tb.style.display="block";
		}
		else
		{
		elem.innerHTML=ajaxreq.responseText;
		tb.style.display="block"
		}
		bxtabs.AlertActiveTab('tab0')
	
	}       
}; 

AjaxGet("<?=$script_name?>?mode=show_xml&file="+file+"&path="+path+"&isutf="+is_utf+"&target=blank",callback);
}

//сохраняем xml заказов
function SaveMe(path)
{
	var load= BX("load");
	var elem = BX("tab2_field");
	var tb=BX("tbl");
	var callback= function(ajaxreq) 
			{
				if (ajaxreq.readyState == 4 && ajaxreq.status == 200)  
				{
					if (ajaxreq.responseText=='')
						elem.innerHTML='Ошибка формирования XML';
					else
					{
						elem.innerHTML=ajaxreq.responseText;
						bxtabs.AlertActiveTab('tab2');
						number.innerHTML=" ";
						tb.style.display="block"
					}					
				}       
			}; 
	AjaxGet("<?=$script_name?>?mode=query&path="+path+'&save=Y',callback);
	load.style.display="none";
}

//получаем список заказов
function GetOrders()
{

var elema = BX("PAYED");
    elemb = BX("DELIVERY");
	elemc = BX("path");
	elem = BX("list");
var r;
r='<?=$script_name?>?path='+elemc.value+'&check=Y';

if (elema.checked==true) r=r+'&PAYED=Y';
if (elemb.checked==true) r=r+'&ALLOW_DELIVERY=Y';
elem.style.display="block";
elem.innerHTML='Загрузка...<img align="center" src="http://gifanimation.ru/images/ludi/17_3.gif" width="30"/>';
elem.innerHTML='<b>Изменения в заказах</b><hr><div class="closeButton" onclick="CloseOrderList()">Х</div><div><iframe style="font-size:11;padding:3;background: white;" width="280" src="'+r+'"></iframe>';
}

//xml в отдельном окне
function _BlankXML(path)
		{
		//alert(path);
		window.open(path,'new','width=700,height=500,toolbar=1 scrollbars=yes');
		}
		
// закрыть список заказов
function CloseOrderList()
{
 BX("list").style.display="none";
}

// неважно
function Hide(event)
{
var element;
if (!event) 
{
	event = window.event;
	element=event.srcElement;
} else {element=event.target}

//document.write(result);
//alert(event);
idlink=element.id.substr(0,2);
if((idlink!="f_")&&(element.id!='e')&&((element.id!='v'))&&((element.id!='d'))&&((element.id!='u'))&&((element.id!='dw'))&&((element.id!='eutf'))&&((element.id!='vu')))
{
menu.style.display="none";
}
}

function JustHide()
{
menu.style.display="none";
}


////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
////////////////////////Mini fileman////////////////////
////////////////////////////////////////////////////////
////////////////////////////////////////////////////////

function CreateFile(name,pathe,workarea)
{
	var pathf=BX(pathe);
	var name=BX(name).value;
	var filelist=createHttpRequest();
	var isdir=BX('isdir').checked;
	globalpath=pathf.value;
	q="<?=$script_name?>?action=createfile&path="+pathf.value+name;
	if (workarea)
	{
		q=q+"&workarea="+workarea;
		fileman=BX(workarea);
	}
	if (isdir==true)
	q=q+"&isdir=Y";
	filelist.open("GET", q, true);
	filelist.onreadystatechange = function() 
	{
            if (filelist.readyState == 4 && filelist.status == 200)  
            {
                if (filelist.responseText=='error001')
                        alert('Файл/папка уже существует, задайте другое имя!');
                if (filelist.responseText=='fail')
                        alert('Файл/папку создать не удалось:(');
                if (filelist.responseText=='success')
                        GetFileList(pathe,workarea);
                fileman.style.display='block';
                load.style.display="none";
             }       
				
	}; 
	filelist.send(null);
}


function GetFileList(pathe,workarea)
{
	var fileman=BX("minifileman")
	var pathf=BX(pathe);
	var search_str=BX('search_str');
	var filelist=createHttpRequest();
	globalpath=pathf.value;
	load.style.display="block";
	if (workarea) 
		fileman=BX(workarea);
	q="<?=$script_name?>?action=getfiles&path="+pathf.value+"&like_str="+search_str.value;

	if (workarea)
	q=q+"&workarea="+workarea;
	filelist.open("GET", q, true);
	filelist.onreadystatechange = function() 
	{
		if (filelist.readyState == 4 && filelist.status == 200)  
		{
		    fileman.innerHTML=filelist.responseText;
			fileman.style.display='block';
			load.style.display="none";
		}       
				
	}; 
	filelist.send(null);
}



function GetFileList2(pathe,workarea)
{
	var fileman=BX("minifileman");
	var search_str=BX('search_str');
	var pathf=BX("path_fileman").value;
	var filelist=createHttpRequest();
        
	if (pathe=="")
	pathe=BX("path_fileman").value;
	globalpath=pathe;
	load.style.display="block";
	if (workarea) 
	fileman=BX(workarea);

	q="<?=$script_name?>?action=getfiles&path="+pathe+"&like_str="+search_str.value;
	if (workarea)
	q=q+"&workarea="+workarea;

	filelist.open("GET",q, true);
	filelist.onreadystatechange = function() 
		{
			if (filelist.readyState == 4 && filelist.status == 200)  
			{
				BX("path_fileman").value=pathe;
				fileman.innerHTML=filelist.responseText;
				load.style.display="none";	
			}       
					
		}; 
	filelist.send(null);
}

function ShowFileMan(event)
{
	if(event.altKey && event.keyCode == 83)
	{
		if (window.document.getSelection) {
		text = document.getSelection();
} else if (document.selection && document.selection.createRange) {
    text = document.selection.createRange().text;
}
	if (text!="")
	{
		  BX('q').value=text;
		  searchbyxmlid();
	}
}
	var dis=BX('test_window').style.display;
    if(event.shiftKey && event.keyCode == 192)
	{
      if (dis=='none'||dis=='') 
	  {
			BX('test_window').style.display='block';GetFileList2('','testfileman');
	  }
	  else 
	  {
			BX('test_window').style.display='none';
      }
    }
}

function InitTabZone(obj)
{
	for (var childItem in obj.childNodes) 
	{
		if (obj.childNodes[childItem].tagName=='DIV')
			alert(object.childNodes[childItem].id);
	}
}

function TabZone(tab_zone_id,field_zone_id,prefix)
{	
	this.tab_inc=0;
	this.prefix=prefix;
	this.active_tab=this.prefix+this.tab_inc;
	var parent_obj=this;
	this.AlertActiveTab=function(tabid)
	{
		if (this.active_tab!=tabid)
			BX(tabid).style.background='#f5dce1';
	};
	
	this.TabCreate=function(tab_name,active)
	{		

		var tab=document.createElement("div");
		var tab_field=document.createElement("div");
		if (!active)
		var active=false;
		tab.className='tab';
		tab.id=parent_obj.prefix+parent_obj.tab_inc;
		parent_obj.tab_inc++;

		tab.style.float='left';
		tab.innerHTML=tab_name;
		tab.onclick=function()
		{
			var tab_id=tab.id;
			var active_tab=parent_obj.active_tab;
			if (parent_obj.active_tab!=false)
			{
				if (BX(parent_obj.active_tab+'_field'))
					BX(parent_obj.active_tab+'_field').style.display="none";
				BX(parent_obj.active_tab).style.background="#B0C4DE";
				BX(parent_obj.active_tab).style.border="1px solid #B0C4DE";
			}
			parent_obj.active_tab=this.id;
			BX(parent_obj.active_tab).style.background="#d3e1fa";
			BX(parent_obj.active_tab).style.border="1px solid #d3e1fa";
			BX(parent_obj.active_tab).style.borderTop="1px solid #B0C4DE";
			if (BX(parent_obj.active_tab+'_field'))
				BX(parent_obj.active_tab+'_field').style.display="block";
		}
		tab_field.id=tab.id+'_field';
		tab_field.style.width='73%';
		tab_field.style.fontSize='12px';
		tab_field.style.height='86%';
		tab_field.style.position='absolute';
		tab_field.style.margin='0px 2px';
		tab_field.style.padding='6px';

		tab_field.style.overflow='auto';
		tab_field.style.background="#d3e1fa";
		tab_field.style.clear="both";

		if (active==false)
		{	
			tab.style.background="#B0C4DE";
			tab_field.style.display="none";
		}
		else 
			tab_field.style.display="block";
		BX(field_zone_id).appendChild(tab_field); 
		BX(tab_zone_id).appendChild(tab);

	};

}

var bxtabs=new TabZone('tab_zone','field_zone','tab');
bxtabs.TabCreate('Файлы',true);
bxtabs.TabCreate('Импорт файлов');
bxtabs.TabCreate('Заказы');
bxtabs.TabCreate('Импорт на удалённом сервере');
BX('tab1_field').appendChild(BX('ipfs'));
BX('tab3_field').appendChild(BX('ext_import'));



//AddWindowRequest('<?=$script_name?>?action=addimpfilewin','custom_windows','ipfs');
//drag'n'drop////

function fixEvent(e) {
	// получить объект событие для IE
	e = e || window.event

	// добавить pageX/pageY для IE
	if ( e.pageX == null && e.clientX != null ) {
		var html = document.documentElement
		var body = document.body
		e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0)
		e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0)
	}

	// добавить which для IE
	if (!e.which && e.button) {
		e.which = e.button & 1 ? 1 : ( e.button & 2 ? 3 : ( e.button & 4 ? 2 : 0 ) )
	}

	return e
}

var dragMaster = (function() {

	var dragObject
	var mouseOffset

	// получить сдвиг target относительно курсора мыши
	function getMouseOffset(target, e) {
		var docPos	= getPosition(target)
		return {x:e.pageX - docPos.x, y:e.pageY - docPos.y}
	}

	function mouseUp(){
		dragObject.style.cursor='default';
		dragObject = null		
		// очистить обработчики, т.к перенос закончен
		document.onmousemove = null
		document.onmouseup = null
		document.ondragstart = null
		document.body.onselectstart = null
	}

	function mouseMove(e){
		e = fixEvent(e)
		
		with(dragObject.style) {
			position = 'fixed'
			cursor='move';
			top = e.pageY - mouseOffset.y + 'px'
			left = e.pageX - mouseOffset.x + 'px'
		}
		return false
	}

	function mouseDown(e) {
		e = fixEvent(e)

		if (e.which!=1 || (("#" + dragObjects.join("#,#") + "#").search("#"+e.target.id+"#") == -1)) return

		dragObject  = this
dragObject.style.cursor='move';
		// получить сдвиг элемента относительно курсора мыши
		mouseOffset = getMouseOffset(this, e)

		// эти обработчики отслеживают процесс и окончание переноса
		document.onmousemove = mouseMove
		document.onmouseup = mouseUp

		// отменить перенос и выделение текста при клике на тексте
		document.ondragstart = function() { return false }
		document.body.onselectstart = function() { return false }

		return false
	}

	return {
		makeDraggable: function(element){
			element.onmousedown = mouseDown
		}
	}

}())

function getPosition(e){
	var left = 0
	var top  = 0

	while (e.offsetParent){
		left += e.offsetLeft
		top  += e.offsetTop
		e	 = e.offsetParent
	}

	left += e.offsetLeft
	top  += e.offsetTop

	return {x:left, y:top}
}


function InitMoveableObjects()
{
	BX.ready(function()
	{
		
		for(var i=0; i<dragObjects.length; i++) {
			dragMaster.makeDraggable(BX(dragObjects[i]))
		} 
	});
}
InitMoveableObjects();

</script>