simple-ots - One-Time Secret
===============
With `simple-ots` you keep sensitive information out of your messages.
`simple-ots` enables you to create messages which are retrievable via a one-time
link.

Once the link has been opened the message will be destroyed by the system, thus
making sure, that you know as soon as a third person intercepted the one-time
link.

How it works
---------------
The data is stored encrypted inside a MySQL table. When posting a message the
system will create a random key `$key` and initialization vector `$iv` for
encryption. The message will be encrypted using the `$key` and `$iv` and
`encrypt()`.

The encrypted message together with `$iv` and a `create_hash($key)` are stored
inside the database and the user is presented an url containing the database id
of the message and the key for decoding the message.

For reading the message the system will load the table row with the given id
and `compare_hash()` the given key with the hash inside the database to verify
that it is the right key to `decrypt()` the message. If the key is correct, the
row will be deleted from the table and the decrypted message will be displayed.

Installation
---------------
All you need is your favorite webserver with PHP support and PHP PDO support.
The webserver should be setup with good SSL settings, otherwise the setup will
not make any sense ;)

Create a directory with write access for the user running the PHP module. Make
sure the path is reachable by PHP, but not being served by the webserver! Or
protect the database by other means, otherwise people are able to download
the database.

The script (poorly) uses the PHP PDO driver, so you should be able to use MySQL
as well or any other supported DB. The table will be named `ots_table`.

Attack Vectors
---------------
- Brute force the secret for a specific id. Speed would depend on the machine
  handling the requests and/or the database, but using the interface would
  result in the message's destruction and the original recipient knows that the
  message has been compromised.
- Capturing the link would be possible, but again using the interface would
  result in the message's destruction.
- With access to the database one would be able to brute force the key more
  directly, but every row has it's own key.
- Since the rows are only deleted it might be possible to reconstruct an already
  destroyed message from the hdd, in case no other data has taken the allocated
  space, yet.
- There is no MAC and encrypted data could therefore be altered without being
  noticed, if an attacker has access to the database. The interface itself does
  not allow updates.
- SQL-Injection seems to be unlikely, since the message given by the user is
  run through the `encrypt()` function first and the `id` and `key` to request a
  message are run through `PDO::prepare()`.

Thanks to
---------------
Jack posting the details on how encryption should be done: http://stackoverflow.com/questions/10916284/how-to-encrypt-decrypt-data-in-php#answer-10945097
