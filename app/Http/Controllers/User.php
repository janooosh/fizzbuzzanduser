<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class User extends Controller
{

    /**
     * returns all users except user from role admin
     * ToDo: return all users from storage/app/users.json. Except users with the role: admin
     * @param Request $request
     */
    public function getUsers(Request  $request)
    {
        //Get and Filter users
        $users = $this->readUsers();
        
        //Filter user and exclude admin role
        $users = $users->filter(function($user) {
            $role = strtolower($user->role);
            return $role !== 'admin';
        })->flatten();

        //Evaluate Request Header
        if($request->header('Accept')=="application/json") {
            return $users->flatten()->toJson(JSON_PRETTY_PRINT);
        }

        return view('users',['users' => $users]);
    }


    /**
     * returns a user except user from role admin
     * ToDo: return a specific user from storage/app/users.json. If the user is role: admin return an empty array
     * @param int $userId
     */
    public function getUser(int $userId)
    {
        //Get Users
        $users = $this->readUsers();

        //Find User By Id
        $user = $users->where('id',$userId)->first();

        if(!$user) {
            return abort(404,"User Not Found");
        }
        if(strtolower($user->role) === 'admin') {
            return [];
        }

        return $user;
    }

    
    /**
     * Will return a collection of users from the "database"
     * and return a Laravel Collection
     * @return Collection
     */
    private function readUsers(): Collection
    {
        $filename = 'users.json';

        //Get File and transform it into a collection
        if(Storage::missing($filename)) {
            return abort(500,"users.json is missing");
        }
        
        $usersArray = json_decode(Storage::get($filename),false);
        $users = collect($usersArray)->flatten();

        /**
         * Transform each user, so that the 'data' properties are stored
         * as named properties in the users collection.
         * E.g. 'data':'role:a|name:b' should result in user->role = 'a' and user->name = 'b'
         * 
         * The implementation is not specific to role and name, so it can easily be extended.
         * However, it requires the data properties of the users to be split with a "|", 
         * while each property item contains a key:value pair that is split by ":".
         */
        $users->each(function($user)
        {
            
            $userData = explode("|",$user->data);
                
            array_walk($userData,function($userDataItem) use ($user) 
            {
                $userPropertyPair = explode(":",$userDataItem);
                
                if(count($userPropertyPair) === 2) 
                {
                    $userPropertyKey = $userPropertyPair[0];
                    $userPropertyValue = $userPropertyPair[1];

                    $user->$userPropertyKey = $userPropertyValue;
                }
                else {
                    //Something is wrong with the propertyItem
                    Log::error("Something is wrong with the data property of a user.",['user_id' => $user->id]);
                }
            });

        });

        return $users;
    }
}
