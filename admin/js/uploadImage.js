// For Multiple Image upload and preview them.
class MultipleUploader 
{
    #multipleUploader;
    #$imagesUploadInput;

    constructor( multiUploaderSelector )
    {
        this.#multipleUploader   = document.querySelector(multiUploaderSelector);
        this.#$imagesUploadInput = document.createElement('input')
    }

    init( { maxUpload = 6 , maxSize = 1 , formSelector = 'form' , filesInpName = 'images'  } = {} )
    {

        const form = document.querySelector(formSelector);

        if (! this.#multipleUploader ) // check if the end user didnt write the multiple uploader div
            throw new Error('The multiple uploader element doesnt exist');

        if (! form ) // check if there is no form with this selector
            throw new Error('We couldn\'t find a form with this selector: ' + formSelector);

        // ensure that the form has enctype attribute with the value multipart/form-data
        form.enctype = 'multipart/form-data'

        if ( document.getElementById('max-upload-number') )
            document.getElementById('max-upload-number').innerHTML = `Upload up to ${ maxUpload } images at a time`;

        //Here we create multiple file input and make it hidden
        this.#$imagesUploadInput.type       = 'file';
        this.#$imagesUploadInput.name       = `${filesInpName}[]`;
        this.#$imagesUploadInput.multiple   = true;
        this.#$imagesUploadInput.accept     = "image/*";
        this.#$imagesUploadInput.style.setProperty('display','none','important');

        // append the newly created input to the form with the help of the formSelector provided by the user
        document.querySelector(formSelector).append( this.#$imagesUploadInput );

        this.#multipleUploader.addEventListener("click", (e) => {

            if ( e.target.className === 'multiple-uploader' || e.target.className === 'mup-msg' || e.target.className === 'mup-main-msg' )
                this.#$imagesUploadInput.click()

        });

        const self = this;

        //Uploaded images preview
        this.#$imagesUploadInput.addEventListener("change", () => {

            if (this.#$imagesUploadInput.files.length > 0) {
                self.#multipleUploader.querySelectorAll('.image-container').forEach(image => image.remove());
                self.#multipleUploader.querySelector('.mup-msg').style.setProperty('display', 'none');
        
                const validFormats = ['image/jpeg', 'image/jpg', 'image/png'];
                const invalidFiles = [];
        
                for (let index = 0; index < this.#$imagesUploadInput.files.length; index++) 
                {
                    const file = this.#$imagesUploadInput.files[index];
        
                    if (!validFormats.includes(file.type)) 
                    {
                        invalidFiles.push(index);
                    }
                }
        
                if (invalidFiles.length > 0) 
                {
                    alert('Invalid file format. Please upload only JPG, JPEG, or PNG files.');
                    invalidFiles.forEach((index) => self.#removeFileFromInput(index, false));
                    document.location ='./gallery.php';
                    return;
                }
        
                const uploadedImagesCount = this.#$imagesUploadInput.files.length > maxUpload ? maxUpload : this.#$imagesUploadInput.files.length;
                const unAcceptableImagesIndices = [];
        
                for (let index = 0; index < uploadedImagesCount; index++) 
                {
                    const imageSize = self.#bytesToSize(this.#$imagesUploadInput.files[index].size);
                    const isImageSizeAcceptable = self.#checkImageSize(index, imageSize, maxSize, 'MB');
        
                    self.#multipleUploader.innerHTML += `
                        <div class="image-container" data-image-index="${index}" id="mup-image-${index}" data-acceptable-image="${+isImageSizeAcceptable}">
                            <div class="image-size"> ${imageSize['size'] + ' ' + imageSize['unit']} </div>
                            ${!isImageSizeAcceptable ? `<div class="exceeded-size"> greater than ${maxSize} MB </div>` : ''}
                            <img src="${URL.createObjectURL(this.#$imagesUploadInput.files[index])}" class="image-preview" alt="" />
                        </div>`;
        
                    if (!isImageSizeAcceptable)
                        unAcceptableImagesIndices.push(index);
                }
        
                unAcceptableImagesIndices.forEach((index) => self.#removeFileFromInput(index, false));
            }
        });

        //For deleting uploaded images
        document.addEventListener('click',(e) => {

            if( e.target.className === 'image-container' ) 
            {
                const imageIndex        = e.target.getAttribute(`data-image-index`)
                const imageIsAcceptable = e.target.getAttribute(`data-acceptable-image`)

                e.target.remove() 

                if ( +imageIsAcceptable )
                    self.#removeFileFromInput(imageIndex)

                if ( document.querySelectorAll('.image-container').length === 0 )
                    self.clear();


                self.#reorderFilesIndices(); 
            }

        });
        return this;
    }

    clear()
    {
        this.#multipleUploader.querySelectorAll('.image-container').forEach( image => image.remove() );
        this.#multipleUploader.querySelectorAll('.mup-msg').forEach( msg => msg.style.setProperty('display', 'flex') );
        this.#$imagesUploadInput.value = [];
    }

    #removeFileFromInput( deletedIndex )
    {
        const dt = new DataTransfer()

        for (const [ index, file] of Object.entries( this.#$imagesUploadInput.files ))
        {
            if ( index != deletedIndex )
                dt.items.add( file )
        }

        this.#$imagesUploadInput.files = dt.files

    }

    #reorderFilesIndices()
    {
        document.querySelectorAll('.image-container').forEach( ( element, index) => {
            element.setAttribute('data-image-index', index.toString() );
            element.setAttribute('id',`mup-image-${ index }`)
        });
    }

    #checkImageSize( imageIndex, imageSize , maxSize)
    {
        return  imageSize['unit'] !== 'MB' || ( imageSize['unit'] === 'MB' && ( imageSize['size'] <= maxSize ) ) ;
    }

    #bytesToSize(bytes)
    {
        const sizes = ['Bytes', 'KB', 'MB']

        const i = parseInt( Math.floor(Math.log(bytes) / Math.log(1024) ), 10)

        if (i === 0)
            return {size: bytes , unit: sizes[i] }
        else
            return {size: (bytes / (1024 ** i)).toFixed(1) , unit: sizes[i] }

    }


}

let multipleUploader = new MultipleUploader('#multiple-uploader').init({
maxUpload : 6, // maximum number of uploaded images
maxSize:1, // size in mb
filesInpName:'images', // input name sent to backend
formSelector: '#my-form', // form selector
});
