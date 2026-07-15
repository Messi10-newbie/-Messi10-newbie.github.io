import javax.swing.*;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.*;
import java.util.ArrayList;
import javax.imageio.ImageIO;
import javax.swing.filechooser.FileNameExtensionFilter;

public class MovieManagerFrame extends JFrame {

    private static final long   serialVersionUID = 1L;
    private static final String MOVIES_FILE      = "movies.txt";

    private final DefaultListModel<String> movieModel   = new DefaultListModel<>();
    private final JList<String>            movieJList   = new JList<>(movieModel);
    private final ArrayList<String>        posterPaths  = new ArrayList<>();

    private JTextField titleField, yearField, posterPathField;
    private JTextArea  descriptionArea;
    private JLabel     posterPreviewLabel;

    public MovieManagerFrame() {
        setTitle("Admin - Movie Manager");
        setSize(950, 700);
        setDefaultCloseOperation(JFrame.DISPOSE_ON_CLOSE);
        setLayout(new BorderLayout(10, 10));

        buildFormPanel();
        loadMoviesFromFile();

        movieJList.setSelectionMode(ListSelectionModel.SINGLE_SELECTION);
        movieJList.addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting() && movieJList.getSelectedValue() != null)
                populateFields(movieJList.getSelectedValue(), movieJList.getSelectedIndex());
        });

        setLocationRelativeTo(null);
    }

    private void buildFormPanel() {
        JPanel formPanel = new JPanel();
        formPanel.setLayout(new BoxLayout(formPanel, BoxLayout.Y_AXIS));
        formPanel.setBorder(BorderFactory.createTitledBorder("Movie Editor"));

        titleField      = new JTextField();
        yearField       = new JTextField();
        descriptionArea = new JTextArea(4, 20);
        descriptionArea.setLineWrap(true);
        descriptionArea.setWrapStyleWord(true);

        posterPathField = new JTextField();
        posterPathField.setEditable(false);

        JButton browseBtn = new JButton("Browse Poster...");
        browseBtn.addActionListener(e -> {
            JFileChooser fc = new JFileChooser();
            fc.setFileFilter(new FileNameExtensionFilter("Image files", "jpg", "jpeg", "png", "gif", "bmp"));
            if (fc.showOpenDialog(this) == JFileChooser.APPROVE_OPTION) {
                posterPathField.setText(fc.getSelectedFile().getAbsolutePath());
                loadPosterPreview(fc.getSelectedFile().getAbsolutePath());
            }
        });

        posterPreviewLabel = new JLabel("No poster", SwingConstants.CENTER);
        posterPreviewLabel.setPreferredSize(new Dimension(160, 220));
        posterPreviewLabel.setMaximumSize(new Dimension(280, 220));
        posterPreviewLabel.setBorder(BorderFactory.createEtchedBorder());

        JButton addBtn    = new JButton("Add Movie");
        JButton editBtn   = new JButton("Update Selected");
        JButton deleteBtn = new JButton("Delete Selected");

        addBtn.addActionListener(e -> addMovie());
        editBtn.addActionListener(e -> editMovie());
        deleteBtn.addActionListener(e -> deleteMovie());

        formPanel.add(new JLabel(" Title:"));        formPanel.add(titleField);
        formPanel.add(new JLabel(" Year:"));         formPanel.add(yearField);
        formPanel.add(new JLabel(" Description:"));  formPanel.add(new JScrollPane(descriptionArea));
        formPanel.add(new JLabel(" Poster File:"));  formPanel.add(posterPathField);
        formPanel.add(browseBtn);
        formPanel.add(posterPreviewLabel);
        formPanel.add(Box.createVerticalStrut(8));
        formPanel.add(addBtn);
        formPanel.add(editBtn);
        formPanel.add(deleteBtn);

        add(new JScrollPane(formPanel), BorderLayout.WEST);
        add(new JScrollPane(movieJList), BorderLayout.CENTER);
    }

    private void populateFields(String selected, int index) {
        try {
            titleField.setText(selected.substring(0, selected.indexOf(" (")));
            yearField.setText(selected.substring(selected.indexOf("(") + 1, selected.indexOf(")")));
            descriptionArea.setText(selected.contains("- Desc: ")
                ? selected.substring(selected.indexOf("- Desc: ") + 8) : "");
        } catch (Exception ex) {
            titleField.setText(selected);
        }
        String poster = (index >= 0 && index < posterPaths.size()) ? posterPaths.get(index) : "";
        posterPathField.setText(poster);
        loadPosterPreview(poster);
    }

    private void addMovie() {
        String title = titleField.getText().trim();
        String year  = yearField.getText().trim();
        String desc  = descriptionArea.getText().trim().replace("\n", " ");
        if (title.isEmpty() || year.isEmpty()) {
            JOptionPane.showMessageDialog(this, "Title and Year cannot be empty!", "Error", JOptionPane.ERROR_MESSAGE);
            return;
        }
        movieModel.addElement(title + " (" + year + ") - Desc: " + desc);
        posterPaths.add(posterPathField.getText().trim());
        saveAllToFile();
        clearFields();
        JOptionPane.showMessageDialog(this, "Movie added successfully!");
    }

    private void editMovie() {
        int index = movieJList.getSelectedIndex();
        if (index == -1) { JOptionPane.showMessageDialog(this, "Please select a movie to update."); return; }
        movieModel.set(index, titleField.getText().trim()
            + " (" + yearField.getText().trim() + ") - Desc: "
            + descriptionArea.getText().trim().replace("\n", " "));
        if (index < posterPaths.size()) posterPaths.set(index, posterPathField.getText().trim());
        else posterPaths.add(posterPathField.getText().trim());
        saveAllToFile();
        JOptionPane.showMessageDialog(this, "Movie updated!");
    }

    private void deleteMovie() {
        int index = movieJList.getSelectedIndex();
        if (index == -1) { JOptionPane.showMessageDialog(this, "Please select a movie to delete."); return; }
        if (JOptionPane.showConfirmDialog(this, "Delete this movie?", "Confirm", JOptionPane.YES_NO_OPTION)
                == JOptionPane.YES_OPTION) {
            movieModel.remove(index);
            if (index < posterPaths.size()) posterPaths.remove(index);
            saveAllToFile();
            clearFields();
        }
    }

    private void clearFields() {
        titleField.setText("");
        yearField.setText("");
        descriptionArea.setText("");
        posterPathField.setText("");
        posterPreviewLabel.setIcon(null);
        posterPreviewLabel.setText("No poster");
    }

    private void loadPosterPreview(String path) {
        if (path == null || path.isBlank()) {
            posterPreviewLabel.setIcon(null);
            posterPreviewLabel.setText("No poster");
            return;
        }
        try {
            BufferedImage img = ImageIO.read(new File(path));
            if (img == null) throw new IOException("Unreadable image");
            posterPreviewLabel.setIcon(new ImageIcon(img.getScaledInstance(150, 210, Image.SCALE_SMOOTH)));
            posterPreviewLabel.setText("");
        } catch (IOException ex) {
            posterPreviewLabel.setIcon(null);
            posterPreviewLabel.setText("Poster not found");
        }
    }

    private void saveAllToFile() {
        try (PrintWriter writer = new PrintWriter(new FileWriter(MOVIES_FILE))) {
            for (int i = 0; i < movieModel.size(); i++) {
                String poster = (i < posterPaths.size()) ? posterPaths.get(i) : "";
                writer.println(poster.isEmpty()
                    ? movieModel.get(i)
                    : movieModel.get(i) + "|POSTER:" + poster);
            }
        } catch (IOException e) {
            JOptionPane.showMessageDialog(this, "Failed to save movies!", "Error", JOptionPane.ERROR_MESSAGE);
        }
    }

    private void loadMoviesFromFile() {
        movieModel.clear();
        posterPaths.clear();
        File file = new File(MOVIES_FILE);
        if (!file.exists()) return;
        try (BufferedReader reader = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.contains("|POSTER:")) {
                    int idx = line.indexOf("|POSTER:");
                    movieModel.addElement(line.substring(0, idx));
                    posterPaths.add(line.substring(idx + 8));
                } else {
                    movieModel.addElement(line);
                    posterPaths.add("");
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading movies: " + e.getMessage());
        }
    }
}
