<?php
 
namespace Drupal\users_module\Form;
 
use Drupal\Core\Form\FormBase;                   // Базовый класс Form API
use Drupal\Core\Form\FormStateInterface;         // Класс отвечает за обработку данных
 
/**
 * Наследуемся от базового класса Form API
 * @see \Drupal\Core\Form\FormBase
 */
class MyForm extends FormBase {

    public $properties = [];

    public function getFormId() {
        return 'my_form';
    }
	
	// метод, который отвечает за саму форму - кнопки, поля
	public function buildForm(array $form, FormStateInterface $form_state) {
		
		//Имя
		$form['firstname'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Имя'),
		];
		
		//Фамилия
		$form['lastname'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Фамилия'),   
		];
		
		//электроная почта
		$form ['email'] = [
			'#type' => 'email',
			'#title' => $this->t('Адрес электроной почты'),
			//'#description' => $this->t('Электроная почта''),     
		];
		
		//тема
		$form ['subject'] = [
			'#type' => 'textfield',
			'#title' => $this->t('Тема'),
			//'#description' => $this->t('Тема обращения'),
		];
	
		$form['message'] = [
			'#type' => 'text_format',
			'#title' => $this->t('Сообщение'), 	
		];
	
	
	
		// Add a submit button that handles the submission of the form.
		$form['actions']['submit'] = [
			'#type' => 'submit',
			'#value' => $this->t('Отправить форму'),
		];
 
		return $form;
	} 

	//валидация 
	public function validateForm(array &$form, FormStateInterface $form_state) {
		
		if (!$form_state->getValue('firstname') || empty($form_state->getValue('firstname'))) {
			$form_state->setErrorByName('firstname', $this->t('Обязательное поле.'));
		}
			
		if (!$form_state->getValue('lastname') || empty($form_state->getValue('lastname'))) {
			$form_state->setErrorByName('lastname', $this->t('Обязательное поле.'));
		}
			
		if (!$form_state->getValue('email') || !filter_var($form_state->getValue('email'), FILTER_VALIDATE_EMAIL)) {
			$form_state->setErrorByName('email', $this->t('Ваш адрес электронной почты не верный.'));
		}
		
		if (!$form_state->getValue('subject') || empty($form_state->getValue('subject'))) {
			$form_state->setErrorByName('subject', $this->t('Тема вашего обращения важнаю'));
		}
			
		if (!$form_state->getValue('message') || empty($form_state->getValue('message'))) {
			$form_state->setErrorByName('message', $this->t('Опишите проблему.'));
		}
	}

	public  function postMail($data){
        $newMail = \Drupal::service('plugin.manager.mail');
        $params['email'] = $data['email'];
        $params['subject'] = $data['subject'];
        $params['message'] = $data['message'];
        $newMail->mail('users_module', 'send_mail', $data['email'], 'en', $params, $reply = NULL, $send = TRUE);
        \Drupal::logger('users_module')->debug('@firstname @lastname sent message.',[
            '@firstname' => $data['firstname'],
            '@lastname'  => $data['lastname'],
        ], 'status');
    }

    public function postContact($data){
        $hapikey = 'ed751717-a4f3-4d09-aa42-19687d7729b5';
        $url = 'https://api.hubapi.com/contacts/v1/contact/createOrUpdate/email/'.$data['email'].'/?hapikey='. $hapikey;

        $arr = [
            'properties' => [

                [
                    'property' => 'firstname',
                    'value' => $data['firstname']
                ],
                [
                    'property' => 'lastname',
                    'value' => $data['lastname']
                ]
            ]
        ];
        $json = json_encode($arr,true);

        $response = \Drupal::httpClient()->post($url.'&_format=hal_json', [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => $json
        ]);

    }

// действия по сабмиту
	public function submitForm(array &$form, FormStateInterface $form_state) {
	
	$data = array(
            'firstname' => $form_state->getValue('firstname'),
            'lastname'  => $form_state->getValue('lastname'),
            'email'     => $form_state->getValue('email'),
            'subject'   => $form_state->getValue('subject'),
            'message'   => $form_state->getValue('message'),
        );

    $this->postMail($data);

    $this->postContact($data);

	drupal_set_message($this->t('Спасибо @firstname @lastname за Ваше сообщение.', [
            '@firstname' => $data['firstname'],
            '@lastname'  => $data['lastname'],
        ]));
	}

}
