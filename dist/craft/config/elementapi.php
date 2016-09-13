<?php
namespace Craft;

return [
	'endpoints' => [
		'api/projects/projects.json' => function() {
			return [
				'elementType' => 'Entry',
				'criteria' => ['section' => 'projects'],
				'transformer' => function(EntryModel $entry) {
					$image = $entry->logo[0] ? $entry->logo[0]->getUrl('medium') : null;

					return [
						'id' => $entry->id,
						'title' => $entry->title,
						'slug' => $entry->slug,
						'bgColor1' => $entry->backgroundColor1,
						'bgColor2' => $entry->backgroundColor2,
						'image' => $image,
					];
				},
			];
		},

		'api/projects/<slug:{slug}>.json' => function($slug) {
			return [
				'elementType' => 'Entry',
				'criteria' => ['section' => 'projects', 'slug' => $slug],
				'transformer' => function(EntryModel $entry) {
					$imageSmall  = $entry->projectImage[0] ? $entry->projectImage[0]->getUrl('small')  : null;
					$imageMedium = $entry->projectImage[0] ? $entry->projectImage[0]->getUrl('medium') : null;
					$imageLarge  = $entry->projectImage[0] ? $entry->projectImage[0]->getUrl('large')  : null;

					$small  = $entry->projectImage[0] ? $entry->projectImage[0]->getWidth('small')  : null;
					$medium = $entry->projectImage[0] ? $entry->projectImage[0]->getWidth('medium') : null;
					$large  = $entry->projectImage[0] ? $entry->projectImage[0]->getWidth('large')  : null;

					$attribution = $entry->attribution[0] ? $entry->attribution[0]->copy : null;

					return [
						'id' => $entry->id,
						'title' => $entry->title,
						'slug' => $entry->slug,
						'projectUrl' => $entry->projectUrl,
						'projectDate' => $entry->projectDate->w3cDate(),
						'copy' => $entry->copy,
						'bgColor1' => $entry->backgroundColor1,
						'bgColor2' => $entry->backgroundColor2,
						'imageSmall' => $imageSmall,
						'imageMedium' => $imageMedium,
						'imageLarge' => $imageLarge,
						'small' => $small,
						'medium' => $medium,
						'large' => $large,
						'attribution' => $attribution
					];
				},
			];
		}
	]
];