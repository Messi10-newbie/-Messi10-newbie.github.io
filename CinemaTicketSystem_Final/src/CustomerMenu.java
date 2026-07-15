import javax.swing.*;
import java.awt.*;
import java.awt.image.BufferedImage;
import java.io.*;
import java.util.HashMap;
import javax.imageio.ImageIO;

public class CustomerMenu extends JFrame {

    private static final long serialVersionUID = 1L;
    private final SeatManager seatManager;
    private final String      currentUsername;
    private final HashMap<String, String> posterMap = new HashMap<>();
    private JLabel posterLabel;

    public CustomerMenu(SeatManager seatManager, String username) {
        this.seatManager     = seatManager;
        this.currentUsername = username;

        setTitle("Welcome, " + username + " - Browse Movies");
        setSize(920, 540);
        setLayout(new BorderLayout());

        DefaultListModel<String> listModel = new DefaultListModel<>();
        JList<String> movieList = new JList<>(listModel);
        loadMovies(listModel);

        posterLabel = new JLabel("Select a movie to view poster", SwingConstants.CENTER);
        posterLabel.setVerticalAlignment(SwingConstants.CENTER);
        posterLabel.setVerticalTextPosition(SwingConstants.BOTTOM);
        posterLabel.setHorizontalTextPosition(SwingConstants.CENTER);

        JPanel posterPanel = new JPanel(new BorderLayout());
        posterPanel.setBorder(BorderFactory.createTitledBorder("Movie Poster"));
        posterPanel.setPreferredSize(new Dimension(270, 0));
        posterPanel.add(posterLabel, BorderLayout.CENTER);

        movieList.addListSelectionListener(e -> {
            if (!e.getValueIsAdjusting()) {
                String sel = movieList.getSelectedValue();
                updatePosterDisplay(sel != null ? posterMap.get(sel) : null);
            }
        });

        JButton bookBtn = new JButton("Book Seats for Selected Movie");
        JButton exitBtn = new JButton("Exit to Login");

        bookBtn.addActionListener(e -> {
            String selected = movieList.getSelectedValue();
            if (selected == null) {
                JOptionPane.showMessageDialog(this, "Please select a movie first!");
                return;
            }
            this.seatManager.loadMovie(selected);
            new SeatSelectionFrame(this.seatManager, selected, currentUsername).setVisible(true);
        });

        exitBtn.addActionListener(e -> {
            dispose();
            Main.showLoginWindow();
        });

        JPanel buttonPanel = new JPanel();
        buttonPanel.add(bookBtn);
        buttonPanel.add(exitBtn);

        add(new JLabel("  Now Showing:", SwingConstants.LEFT), BorderLayout.NORTH);
        add(new JScrollPane(movieList), BorderLayout.CENTER);
        add(posterPanel, BorderLayout.EAST);
        add(buttonPanel, BorderLayout.SOUTH);
        setLocationRelativeTo(null);
    }

    private void loadMovies(DefaultListModel<String> model) {
        posterMap.clear();
        File file = new File("movies.txt");
        if (!file.exists()) {
            model.addElement("No movies available yet.");
            return;
        }
        try (BufferedReader reader = new BufferedReader(new FileReader(file))) {
            String line;
            while ((line = reader.readLine()) != null) {
                if (line.contains("|POSTER:")) {
                    int idx = line.indexOf("|POSTER:");
                    String display = line.substring(0, idx);
                    String poster  = line.substring(idx + 8);
                    posterMap.put(display, poster);
                    model.addElement(display);
                } else {
                    model.addElement(line);
                }
            }
        } catch (IOException e) {
            System.err.println("Error loading movies: " + e.getMessage());
            model.addElement("Error loading movies.");
        }
    }

    private void updatePosterDisplay(String path) {
        if (path == null || path.isBlank()) {
            posterLabel.setIcon(null);
            posterLabel.setText("No poster available");
            return;
        }
        try {
            BufferedImage img = ImageIO.read(new File(path));
            if (img == null) throw new IOException("Unreadable image");
            Image scaled = img.getScaledInstance(240, 340, Image.SCALE_SMOOTH);
            posterLabel.setIcon(new ImageIcon(scaled));
            posterLabel.setText("");
        } catch (IOException ex) {
            posterLabel.setIcon(null);
            posterLabel.setText("Poster not found");
        }
    }
}
