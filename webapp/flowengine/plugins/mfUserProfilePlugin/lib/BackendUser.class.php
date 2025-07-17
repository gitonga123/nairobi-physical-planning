<?php 

class BackendUser
{
    static public function methodNotFound(sfEvent $event)
    {
        if (method_exists('BackendUser', $event['method']))
        {
            $event->setReturnValue(call_user_func_array(
                array('BackendUser', $event['method']),
                array_merge(array($event->getSubject()), $event['arguments'])
            ));
            return true;
        }
    }

    static public function mfHasCredential($method, $credential)
	{
		$roles = sfContext::getInstance()->getUser()->getCredentials();

		if(in_array($credential, $roles))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	static public function hasMfGroup($method, $id, $user_id)
	{
		$q = Doctrine_Query::create()
			->from('MfGuardUserGroup a')
			->where('a.user_id = ?', $user_id)
			->andWhere('a.group_id = ?', $id);
		$usergroups = $q->execute();
		if(sizeof($usergroups) > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
}