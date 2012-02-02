<?

foreach($arResult["ITEMS"] as $key => $arElement)
{
	if(is_array($arElement["PREVIEW_PICTURE"]))
	{
		$arFileTmp = CFile::ResizeImageGet(
			$arElement['PREVIEW_PICTURE'],
			array("width" => 110, 'height' => 100),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			false
		);
		$arSize = getimagesize($_SERVER["DOCUMENT_ROOT"].$arFileTmp["src"]);

		$arElement['PREVIEW_PICTURE'] = array(
			'SRC' => $arFileTmp["src"],
			'WIDTH' => IntVal($arSize[0]),
			'HEIGHT' => IntVal($arSize[1]),
		);
		$arResult["ITEMS"][$key] = $arElement;
	}

}