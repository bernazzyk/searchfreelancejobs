<?php

$string = "%7B%22categoryID%22%3A4%2C%22subcategoryID%22%3A0%2C%22filters%22%3A%5B1%2C2%2C3%2C4%2C5%2C6%2C7%2C8%2C9%2C10%2C11%2C12%2C13%5D%2C%22pageNumber%22%3A2%2C%22sortExpression%22%3A%7B%22PropertyName%22%3A%22BiddingEndTime%22%2C%22DisplayValue%22%3A%22Bidding%20Ends%22%2C%22Direction%22%3A0%2C%22Previous%22%3Anull%7D%2C%22featuredFirst%22%3Atrue%7D";

$out = urldecode( $string );

print_r( json_decode( $out ));

