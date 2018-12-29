<?php

$db_prefix = 'vk_';
$db_attr = array(
  'i11' =>  'int(11)',
  'i10' =>  'int(10)',
  'mi8' =>  'mediumint(8)',
  'si6' =>  'smallint(6)',
  'si5' =>  'smallint(5)',
  'ti4' =>  'tinyint(4)',
  'v255' => 'varchar(255)',
  'v50' =>  'varchar(50)',
  'v40' =>  'varchar(40)',
  'v25' =>  'varchar(25)',
  'tx' =>   'text',
  'b' =>    'bool'
);

/*
    '' => array(
	'' => array(
	    'type' => '',
	    'desc' => ''
	)
    )
*/
$db = array(
    'albums' => array(
	'id' => array(
	    'type' => 'i10',
	    'desc' => 'ID альбома'
	),
	'name' => array(
	    'type' => 'v255',
	    'desc' => 'Название альбома'
	),
	'created' => array(
	    'type' => 'i10',
	    'desc' => 'Дата создания альбома (UNIX time)'
	),
	'updated' => array(
	    'type' => 'i10',
	    'desc' => 'Дата последнего обновления (UNIX time)'
	),
	'img_total' => array(
	    'type' => 'i10',
	    'desc' => 'Количество фотографий из VK.API'
	),
	'img_done' => array(
	    'type' => 'i10',
	    'desc' => 'Количество сохраненных фото'
	)
    ),
    'attach' => array(
	'uid' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'wall_id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'type' => array(
	    'type' => 'v255',
	    'desc' => 'Тип вложения. Может принимать значения: '
	),
	'is_local' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что вложение найдено локально'
	),
	'attach_id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'owner_id' => array(
	    'type' => 'i11',
	    'desc' => 'ID владельца'
	),
	'uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'path' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'width' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'height' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'text' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'date' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'access_key' => array(
	    'type' => 'v255',
	    'desc' => 'Ключ доступа к вложению (если необходимо)'
	),
	'title' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'duration' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'player' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'link_url' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'caption' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'skipthis' => array(
	    'type' => 'b',
	    'desc' => 'Флаг пропуска элемента в очереди закачки (например если элемент возвращает ошибку 404)'
	)
    ),
    'counters' => array(
	'album' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'photo' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'music' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'video' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'wall' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'docs' => array(
	    'type' => 'mi8',
	    'desc' => ''
	),
	'dialogs' => array(
	    'type' => 'mi8',
	    'desc' => ''
	)
    ),
    'dialogs' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'date' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'title' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'in_read' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'multichat' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что диалог является групповым'
	),
	'chat_id' => array(
	    'type' => 'i11',
	    'desc' => 'ID группового диалога'
	),
	'admin_id' => array(
	    'type' => 'i11',
	    'desc' => 'ID последнего активного пользователя диалога'
	),
	'users' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'is_new' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что диалог является новым'
	),
	'is_upd' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что диалог имеет новые сообщения'
	)
    ),
    'docs' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'owner_id' => array(
	    'type' => 'i11',
	    'desc' => 'ID владельца'
	),
	'title' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'size' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'ext' => array(
	    'type' => 'v25',
	    'desc' => ''
	),
	'uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'date' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'type' => array(
	    'type' => 'si6',
	    'desc' => ''
	),
	'preview_uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'preview_path' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'width' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'height' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'deleted' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что элемент был удалён в ВК'
	),
	'in_queue' => array(
	    'type' => 'b',
	    'desc' => 'Флаг что элемент находится в очереди закачки'
	),
	'local_path' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'local_size' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'local_w' => array(
	    'type' => 'si6',
	    'desc' => ''
	),
	'local_h' => array(
	    'type' => 'si6',
	    'desc' => ''
	)
    ),
    'groups' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'name' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'nick' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'photo_uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'photo_path' => array(
	    'type' => 'v255',
	    'desc' => ''
	)
    ),
    'messages' => array(
		'uid' => array(
			'type' => 'i11',
		    'desc' => 'Уникальный ID'
		),
		'msg_id' => array(
			'type' => 'i11',
		    'desc' => 'ID сообщения (для пересылаемых сообшений имеет негативное ID сообщения)'
		),
		'msg_chat' => array(
			'type' => 'i11',
		    'desc' => 'ID группового чата которому принадлежит сообщение'
		),
		'msg_dialog' => array(
			'type' => 'i11',
		    'desc' => 'ID диалога которому принадлежит сообщение'
		),
		'msg_user' => array(
			'type' => 'i11',
			'desc' => 'ID пользователя который оставил сообщение'
		),
		'msg_date' => array(
			'type' => 'i11',
		    'desc' => 'Дата сообщения'
		),
		'msg_body' => array(
			'type' => 'tx',
		    'desc' => 'Тело сообщения'
		),
		'msg_attach' => array(
		    'type' => 'b',
		    'desc' => 'Флаг: Сообщение содержит вложение (связь `msg_id` -> `'.$db_prefix.'messages_attach.wall_id` )'
		),
		'msg_forwarded' => array(
			'type' => 'b',
			'desc' => 'Флаг: Сообщение содержит пересылаемое(ые) сообщения'
		)
    ),
    'messages_attach' => array(
	'uid' => array(
	    'type' => 'i11',
	    'desc' => 'Автоинкремент ID'
	),
	'wall_id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'type' => array(
	    'type' => 'v255',
	    'desc' => 'Тип вложения. Может принимать значения: doc, photo, sticker, video, link, wall'
	),
	'is_local' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'attach_id' => array(
	    'type' => 'i11',
	    'desc' => 'ID вложения. Для типа `wall` указывает на связь `attach_id` => '.$db_prefix.'messages_wall.id'
	),
	'owner_id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'path' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'width' => array(
	    'type' => 'si5',
	    'desc' => 'Ширина изображения'
	),
	'height' => array(
	    'type' => 'si5',
	    'desc' => 'Высота изображения'
	),
	'text' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'date' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'access_key' => array(
	    'type' => 'v255',
	    'desc' => 'Поле `access key` для доступа к не публичному содержанию'
	),
	'title' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'duration' => array(
	    'type' => 'i11',
	    'desc' => 'Тип `wall` - является флагом сохранена ли запись; Тип `doc` - размер документа в байтах; Тип `video` - продолжительность сек.'
	),
	'player' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'link_url' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'caption' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'skipthis' => array(
	    'type' => 'b',
	    'desc' => 'Флаг пропуска элемента в очереди закачки'
	)
    ),
    'music' => array(
	'id' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'artist' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'title' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'album' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'duration' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'date_added' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'date_done' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'saved' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'deleted' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'path' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'hash' => array(
	    'type' => 'v40',
	    'desc' => ''
	),
	'in_queue' => array(
	    'type' => 'b',
	    'desc' => ''
	)
    ),
    'music_albums' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'name' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'deleted' => array(
	    'type' => 'b',
	    'desc' => ''
	)
    ),
    'photos' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'album_id' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'date_added' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'width' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'height' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'date_done' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'saved' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'path' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'hash' => array(
	    'type' => 'v40',
	    'desc' => ''
	),
	'in_queue' => array(
	    'type' => 'b',
	    'desc' => ''
	)
    ),
    'profiles' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'first_name' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'last_name' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'sex' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'nick' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'photo_uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'photo_path' => array(
	    'type' => 'v255',
	    'desc' => ''
	)
    ),
    'session' => array(
	'vk_id' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'vk_token' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'vk_expire' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'vk_user' => array(
	    'type' => 'i11',
	    'desc' => ''
	)
    ),
    'status' => array(
	'key' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'val' => array(
	    'type' => 'tx',
	    'desc' => ''
	)
    ),
    'stickers' => array(
	'product' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'sticker' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'width' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'height' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'uri' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'path' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'in_queue' => array(
	    'type' => 'ti4',
	    'desc' => ''
	)
    ),
    'videos' => array(
	'id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'owner_id' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'title' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'desc' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'duration' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'preview_uri' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'preview_path' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'player_uri' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'access_key' => array(
	    'type' => 'v255',
	    'desc' => ''
	),
	'date_added' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'date_done' => array(
	    'type' => 'i10',
	    'desc' => ''
	),
	'deleted' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'in_queue' => array(
	    'type' => 'b',
	    'desc' => ''
	),
	'local_path' => array(
	    'type' => 'tx',
	    'desc' => ''
	),
	'local_size' => array(
	    'type' => 'i11',
	    'desc' => ''
	),
	'local_format' => array(
	    'type' => 'v50',
	    'desc' => ''
	),
	'local_w' => array(
	    'type' => 'si5',
	    'desc' => ''
	),
	'local_h' => array(
	    'type' => 'si5',
	    'desc' => ''
	)
    ),
    'wall' => array(
		'id' => array(
			'type' => 'i11',
			'desc' => 'ID сообщения'
		),
		'from_id' => array(
		    'type' => 'i11',
			'desc' => ''
		),
		'owner_id' => array(
		    'type' => 'i11',
			'desc' => ''
		),
		'date' => array(
		    'type' => 'i11',
			'desc' => 'Дата сообщения'
		),
		'post_type' => array(
		    'type' => 'v255',
			'desc' => 'Тип сообщения'
		),
		'text' => array(
			'type' => 'tx',
			'desc' => 'Содержание сообщения'
		),
		'attach' => array(
		    'type' => 'b',
			'desc' => 'Сообщение содержит вложение (связь id -> '.$db_prefix.'attach.wall_id )'
		),
		'repost' => array(
		    'type' => 'i11',
			'desc' => 'ID сообщения репоста (связь repost -> id )'
		),
		'repost_owner' => array(
		    'type' => 'i11',
			'desc' => 'ID владельца репост сообщения (связь repost_owner -> owner_id )'
		),
		'is_repost' => array(
		    'type' => 'b',
			'desc' => 'Сообщение содержит репост'
		)
    ),
);

?>