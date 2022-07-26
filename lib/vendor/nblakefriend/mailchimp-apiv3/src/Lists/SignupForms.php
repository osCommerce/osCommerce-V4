<?php
namespace MailChimp\Lists;

class SignupForms extends Lists
{

    /**
     * Get a list of signup form values
     *
     * @param string $list_id
     * @param array $query (See Above) OPTIONAL associative array of query parameters.
     * @return object
    */
    public function getSignupForms($list_id, array $query = [])
    {
        return self::execute("GET", "lists/{$list_id}/signup-forms", $query);
    }

    /**
     * customize a lists signup form values
     *
     * @param string $list_id
     * @param array $data (See Above) OPTIONAL associative array of query parameters.
     * @return object
    */
    public function editSignupForm($list_id, array $data = [])
    {
        return self::execute("POST", "lists/{$list_id}/signup-forms", $data);
    }

}
