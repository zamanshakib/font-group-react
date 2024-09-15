<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Font Upload Management By Shakib</title>
    <!-- React and ReactDOM from CDN -->
    <script src="https://unpkg.com/react@17/umd/react.production.min.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.production.min.js" crossorigin></script>
    <!-- Babel to compile JSX -->
    <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
    <!-- Sweetalert2  -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Bootstrap  -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Styling for the drop zone and file input */
        .drop-zone {
            width: 100%;
            padding: 20px;
            border: 2px dashed #ccc;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
            background-color: #f9f9f9;
        }

        .drop-zone.dragging {
            background-color: #d3e4ff;
            border-color: #0062cc;
        }

        .font-list {
            margin: 10px 0;
        }

        .font-list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .file-input {
            display: none;
        }
    </style>
</head>

<body>

    <div id="root"></div>

    <!-- React component script -->
    <script type="text/babel">

        const { useState,useEffect } = React;

function FontManager() {
    const [groupName, setGroupName] = useState('');
    const [fonts, setFonts] = useState([]);
    const [fontGroupList, setFontGroupList] = useState([]);
    const [selectedFonts, setSelectedFonts] = useState([]);
    const [isDragging, setIsDragging] = useState(false);
    const [previewFont, setPreviewFont] = useState(null);
    const [isEditMode, setIsEditMode] = useState(false);
    const [groupFonts, setGroupFonts] = useState([{ fontId: '' }]);
    const [editingGroupId, setEditingGroupId] = useState(null);
    const [items, setItems] = useState([]); 

    useEffect(() => {
        handleFetch(); // Fetch fonts on component mount
    }, []);

    useEffect(() => {
        handlefontGroupListFetch(); // Fetch font group list whenever fonts change
    }, [fonts]);

    const addRow = () => {
        setGroupFonts([...groupFonts, { fontId: '' }]);
    };

    // Remove a row based on index
    const removeRow = (index) => {
        const updatedGroupFonts = [...groupFonts];
        updatedGroupFonts.splice(index, 1); // Remove the row
        setGroupFonts(updatedGroupFonts);
    };
    const handleFetch = async () => {
        fetch('FontFetch.php', {
            method: 'GET',
        })
        .then(response => response.json())
        .then(data => {
            console.log('Data Fetch Done');
            console.log(data);
            if (data.status === 'success') {
                setFonts(data.fonts); // Update the fonts state
            } else {
                
                alert('Error fetching data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error fetching fonts: ' + error);
        });
    };
    const handlefontGroupListFetch = async() => {
        fetch('FontGroupFetch.php', {
            method: 'GET',
        })
        .then(response => response.json())
        .then(data => {
            console.log('Group Data Fetch Done');
            console.log(data);
            if (data.status === 'success') {
                setFontGroupList(data.fonts); // Update the fonts state
            } else {
                alert('Error fetching data: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error fetching fonts: ' + error);
        });
    };
    const handleGroupNameChange  = (e)=>{
        setGroupName(e.target.value);

    }
    // Handle file upload via file input or drop
    const handleFileUpload = (files) => {
        const file = files[0];
        if (file && file.name.endsWith('.ttf')) {
            const formData = new FormData();
            formData.append('font', file);

            // Send the form data to the PHP backend
            fetch('FontUploader.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const fontDetails = {
                        id: data.fontId,
                        name: file.name,
                        path: data.filePath
                    };
                    setFonts([...fonts, fontDetails]); // Update the fonts state

                    Swal.fire({
                        position: "top-end",
                        icon: "success",
                        title: "Fond Uploaded Successfully",
                        showConfirmButton: false,
                        timer: 1500
                        });
                } else {
                    console.log(data.message);
                    Swal.fire({
                        icon: "error",
                        title: "Data Upload error",
                        text: data.message,
                        });
                }
            })
            .catch(error => {
                alert('Error uploading font: ' + error);
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Please upload a valid .ttf file",
                });
        }
    };

    // Handle file drop
    const handleDrop = (event) => {
        event.preventDefault();
        setIsDragging(false);
        const files = event.dataTransfer.files;
        handleFileUpload(files);
    };

    // Handle drag over event
    const handleDragOver = (event) => {
        event.preventDefault();
        setIsDragging(true);
    };

    // Handle drag leave event
    const handleDragLeave = () => {
        setIsDragging(false);
    };

    // Handle click to open file input
    const handleClick = () => {
        document.getElementById('file-upload').click();
    };

    // Handle font preview
    const handlePreview = (font) => {
        // Remove existing font style if any
        const existingStyle = document.getElementById('font-preview-style');
        if (existingStyle) {
            document.head.removeChild(existingStyle);
        }

        // Create a new style element for the @font-face rule
        const style = document.createElement('style');
        style.id = 'font-preview-style';
        style.type = 'text/css';

        // Define @font-face rule
        const fontFaceRule = `
            @font-face {
                font-family: 'UploadedFont';
                src: url('${font.path}') format('truetype');
            }
        `;
        
        style.innerHTML = fontFaceRule;
        document.head.appendChild(style);

        // Apply the font for preview
        setPreviewFont({
            name: font.name,
            path: font.path,
            loaded: true
        });
        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "Preview Is loading...",
            showConfirmButton: false,
            timer: 1500
            });
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        const groupData = {
            groupName,
            groupFonts,
        };
        console.log(groupData);
        if(groupData.groupFonts.length<2){
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Sorry You have to select at least 2 fonts",
                });
        }else{
            const url = isEditMode ? 'FontGroupUpdate.php' : 'FontGroupSave.php';

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ groupId: editingGroupId, ...groupData }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        setIsEditMode(false);
                        setEditingGroupId(null);
                        setGroupName('');
                        setGroupFonts([{ fontId: '' }]);
                        handlefontGroupListFetch(); // Refresh the group list

                        Swal.fire({
                            position: "top-end",
                            icon: "success",
                            title: "Font group saved successfully!",
                            showConfirmButton: false,
                            timer: 1500
                            });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error saving font group: ",
                            text: data.message,
                            });
                    }
                })
                .catch(error => {
                    Swal.fire({
                            icon: "error",
                            title: "Error saving font group: ",
                            text: data.message,
                            });
                });
        }
        
    };

    const handleFontChange = (index, e)=>{
        const updatedGroupFonts = [...groupFonts];
        const selectedFontId = e.target.value;

        // Check if the font is already selected in another row (excluding the current index)
        const isFontAlreadySelected = updatedGroupFonts.some((font, i) => font.fontId === selectedFontId && i !== index);

        if (isFontAlreadySelected) {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Font already added!",
                });
        } else {
            updatedGroupFonts[index].fontId = selectedFontId;
            setGroupFonts(updatedGroupFonts);
        }

    }
    const handleDelete = (fontId) => {

        Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, delete it!"
        }).then((result) => {
        if (result.isConfirmed) {
            fetch('FontDelete.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: fontId }), // Sending font ID as JSON
        })
        .then(response => response.json()) // Parse the response JSON
        .then(data => {
            if (data.status === 'success') {
                // Remove the deleted font from the state by filtering the fonts array
                setFonts(fonts.filter((font) => font.id !== fontId));
                Swal.fire({
                    title: "Deleted!",
                    text: "Your Font has been deleted.",
                    icon: "success"
                    });
            } else {
                alert('Error deleting font: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error deleting font: ' + error);
        });
        }
        });
        
    };
    const handleGroupDelete = (groupId) => {

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
            }).then((result) => {
            if (result.isConfirmed) {

                fetch('FontGroupDelete.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: groupId }), // Sending font ID as JSON
            })
            .then(response => response.json()) // Parse the response JSON
            .then(data => {
                if (data.status === 'success') {
                    // Remove the deleted font from the state by filtering the fonts array
                    setFontGroupList(fontGroupList.filter((group) => group.id !== groupId));
                    Swal.fire({
                    title: "Deleted!",
                    text: "Your file has been deleted.",
                    icon: "success"
                    });
                } else {
                    alert('Error deleting font group: ' + data.message);
                }
            })
            .catch(error => {
                alert('Error deleting font group: ' + error);
            });
            }
            });

        
    };
    // Handle font grouping (mock example)
    const handleGroupFonts = () => {
        if (selectedFonts.length === 0) {
            alert('Please select at least one font to group');
        } else {
            alert(`Selected fonts for grouping: ${selectedFonts.join(', ')}`);
        }
    };

    const handleEditFormGroup = (groupId) => {
        const groupToEdit = fontGroupList.find((group) => group.id === groupId);
        console.log(groupToEdit);

        if (groupToEdit) {
            setGroupName(groupToEdit.name);
            setGroupFonts(groupToEdit.font_ids.map(fontId => ({ fontId })));
            setIsEditMode(true);
            setEditingGroupId(groupId);
            Swal.fire({
                position: "top-end",
                icon: "success",
                title: "Font group retrive for Edit ",
                showConfirmButton: false,
                timer: 1500
                });
        }
    };

    return (
        <div>
        <div class="container">
            <div
                className={`drop-zone ${isDragging ? 'dragging' : ''}`}
                onDrop={handleDrop}
                onDragOver={handleDragOver}
                onDragLeave={handleDragLeave}
                onClick={handleClick}
            >
                Drag and drop or click here to upload a .ttf file
                <input 
                    type="file" 
                    id="file-upload" 
                    className="file-input" 
                    onChange={(e) => handleFileUpload(e.target.files)} 
                    accept=".ttf" 
                />
            </div>
            {/* Font Preview Section */}
            {previewFont && previewFont.loaded && (
                <div style={{ marginTop: '20px' }}>
                    <h3>Font Preview</h3>
                    <p style={{ fontFamily: 'UploadedFont' }}>
                        This is a preview of the font: {previewFont.name}
                    </p>
                </div>
            )}
            {/* Font List Table */}

            <h3 class="text-center">Font List</h3>

            <table class="table table-border">
                <thead>
                    <tr>
                        <th>Font Name</th>
                        <th>Preview</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {fonts.map((font, index) => (
                        <tr key={index}>
                            <td>{font.name}</td>
                            <td>
                                <button onClick={() => handlePreview(font)}>Preview</button>
                            </td>
                            <td>
                            <button class="btn btn-danger" onClick={() => handleDelete(font.id)}>Delete</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
            

            <div className="container mt-4">
            <h3>{isEditMode ? 'Edit Font Group' : 'Create Font Group'}</h3>
            <form onSubmit={handleSubmit}>
                <div className="mb-3">
                    <label className="form-label">Group Name</label>
                    <input
                        type="text"
                        className="form-control"
                        value={groupName}
                        onChange={handleGroupNameChange}
                        placeholder="Enter group name"
                        required
                    />
                </div>

                <div class="row">
                    <div className="mb-3">
                        <label className="form-label">Select Fonts for Group</label>
                        {groupFonts.map((groupFont, index) => (
                            <div key={index} className="input-group mb-2">
                                <select
                                    className="form-select"
                                    value={groupFont.fontId}
                                    onChange={(e) => handleFontChange(index, e)}
                                    required
                                >
                                    <option value="">-- Select Font --</option>
                                    {fonts.map((font) => (
                                        <option key={font.id} value={font.id}>
                                            {font.name}
                                        </option>
                                    ))}
                                </select>
                                {groupFonts.length > 1 && (
                                    <button
                                        type="button"
                                        className="btn btn-danger"
                                        onClick={() => removeRow(index)}
                                    >
                                        Delete
                                    </button>
                                )}
                            </div>
                        ))}
                    </div>

                    <button type="button" className=" col-md-2 btn btn-secondary mb-3" onClick={addRow}>
                        Add More
                    </button>
                </div>
                <button type="submit" className="col-md-2 btn btn-primary mb-3">
                    {isEditMode ? "Update Group" : "Create Group"}
                </button>
            </form>

            <h3 class="text-center">Font Group List</h3>
            <table class="table table-border">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Fonts</th>
                        <th>Count</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {fontGroupList.map((group, index) => (
                        <tr key={index}>
                            <td>{group.id}</td>
                            <td>{group.name}</td>
                            <td>
                            {group.font_names}
                                
                            </td>
                            <td>
                                {group.font_count}
                            </td>
                            <td>
                            <button class="btn btn-info" onClick={() => handleEditFormGroup(group.id)}>Edit</button>
                            <button class="btn btn-danger" onClick={() => handleGroupDelete(group.id)}>Delete</button>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
            <div class="footer card p-2 text-center">
            <p>Assignment Submit BY:</p>
            <h3>Md: Shakib Uz-Zaman</h3>
            <p>Phone: 01672572177</p>
            <p>Email: zaman.shakib@gmail.com</p>
            </div>
        </div>

            
        </div>
    );
}
    // Render the React component
    ReactDOM.render(<FontManager />, document.getElementById('root'));
    </script>
</body>

</html>