export function toTitleCase(str) {
    str = str.replace(/_/g, " ");
    return str.replace(/\w\S*/g, function(txt){
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}
export function shortenText(str, length) {
    if (str.length > length) {
        return str.substring(0, length) + "...";
    } else {
        return str;
    }
}