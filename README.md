# XPATH-Generator
This project is actually in Development and has an experimental state. Validate the Results against your xml files

## Supported Formats
Actually this php library can only handle one xml file passed to stdin.

## How To
### Docker

If you want to use this little library as docker image so perfor

```shell
docker build -t xpathgenerator .
```

Now try to pass a xml file to the script using

```shell
cat your/file.xml | docker run --rm -i xpathgenerator
``` 

### Local

If you want to use this little library on your local machine so perform `cat your/file.xml | php main.php`


## Handling Output
To Handle the Output you can easily grep stdout. 
The Output will contain the xml tags in json format including value and the xpath.

## Toughts

So feel free to fork this project and continue the work. 