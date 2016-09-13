<?php namespace Craft;

class Perform_ExportController extends BaseController
{

	public function actionExportIndex()
	{
		$this->renderTemplate('perform/export/index', array(
			'forms' => craft()->perform_forms->getAllForms()
		));
	}


	public function actionCsv()
	{
		$dataToProcess = true;
		$first = true;
		$offset = 0;
		$blocksize = 500;
		$maximumLoops = 1000;
		$loops = 0;

		$formId = craft()->request->getPost('form');
		$form = craft()->perform_forms->getFormById($formId);

		set_time_limit('1000');
		//ini_set('memory_limit', '1024M');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . ($form->handle . '_submissions.csv'));
		header('Content-Transfer-Encoding: binary');
		$stream = fopen('php://output', 'w');

		while ($dataToProcess && $loops <= $maximumLoops)
		{
			$dataToProcess = false;
			$loops++;

			$criteria = craft()->elements->getCriteria('Perform_Submission');
			$criteria->formId = $formId;
			$criteria->limit = $blocksize;
			$criteria->offset = $offset;

			if (isset($_POST['fromDate']) &&
				!empty($_POST['fromDate']['date']) &&
				isset($_POST['toDate']) &&
				!empty($_POST['toDate']['date'])
			) {

				$fromDate = craft()->request->getPost('fromDate');
				$fromDate = DateTime::createFromString($fromDate, craft()->timezone);
				$toDate = craft()->request->getPost('toDate');
				$toDate = DateTime::createFromString($toDate, craft()->timezone);
				//A bit of a hack, I can't work out how to do a betweendates or AND query. These fields are always the same anyway
				$criteria->dateCreated = '>= ' . $fromDate->format(DateTime::MYSQL_DATETIME);
				$criteria->dateUpdated = '<= ' . $toDate->format(DateTime::MYSQL_DATETIME);
			} elseif (isset($_POST['fromDate']) && !empty($_POST['fromDate']['date'])) {
				$fromDate = craft()->request->getPost('fromDate');
				$fromDate = DateTime::createFromString($fromDate, craft()->timezone);
				$criteria->dateCreated = '>= ' . $fromDate->format(DateTime::MYSQL_DATETIME);
			} else if (isset($_POST['toDate']) && !empty($_POST['toDate']['date'])) {
				$toDate = craft()->request->getPost('toDate');
				$toDate = DateTime::createFromString($toDate, craft()->timezone);
				$criteria->dateCreated = '<= ' . $toDate->format(DateTime::MYSQL_DATETIME);
			}

			$criteria->order = 'dateCreated desc';

			// Write column names first.

			$first = true;

			foreach ($criteria->find() as $submission) 
			{
				$dataToProcess = true;
				$row = array(
					'Id' => $submission->id,
					'Time' => $submission->dateCreated->format('d/m/Y H:i:s')
				);

				foreach($form->getQuestions() as $question)
				{
					$columnName = str_replace($form->handle . '_', '', $question->handle);
					$columnName = str_replace(Perform_QuestionType::DropdownEmailRouterHandle, '', $columnName);
					$columnName = str_replace(Perform_QuestionType::RadioButtonsEmailRouterHandle, '', $columnName);
					$columnName = ucwords($columnName);

					$row[$columnName] = $submission->{$question->handle};
					$value = $submission->{$question->handle};
					
					if ($value instanceof MultiOptionsFieldData)
					{
						$options = $value->getOptions();

						$summary = array();
						
						for ($j = 0; $j < count($options); ++$j)
						{
							$option = $options[$j];
							if ($option->selected)
								$summary[] = $option->value;
						}

						$row[$columnName] = implode($summary, ', ');
					}
					elseif ($question->type == Perform_QuestionType::MultilineText)
					{
						$row[$columnName] = str_replace('<br />', "\n", $value);
					}
					else
					{
						$row[$columnName] = $value;
					}
				}

				if ($first) {
					fputcsv($stream, array_keys($row));
					$first = false;
				}

				fputcsv($stream, $row);

			}
			$first = true;
			$offset += $blocksize;

		}

		fclose($stream);
	}

}