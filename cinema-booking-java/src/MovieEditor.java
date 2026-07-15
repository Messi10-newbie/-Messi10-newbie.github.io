import javafx.geometry.*;
import javafx.scene.*;
import javafx.scene.control.*;
import javafx.scene.layout.*;
import javafx.scene.paint.Color;
import javafx.scene.text.*;
import javafx.stage.*;

public class MovieEditor {

    public static void showAddDialog(Movie existing, Runnable onSave) {
        showDialog(existing, false, onSave);
    }

    public static void showEditDialog(Movie existing, Runnable onSave) {
        showDialog(existing, true, onSave);
    }

    private static void showDialog(Movie existing, boolean isEdit, Runnable onSave) {
        Stage dlg = new Stage();
        dlg.setTitle(isEdit ? "Edit Movie" : "Add New Movie");
        dlg.initOwner(Main.primaryStage);
        dlg.initModality(Modality.APPLICATION_MODAL);

        VBox root = new VBox(14);
        root.setPadding(new Insets(30));
        root.setStyle("-fx-background-color: #161b22;");
        root.setPrefWidth(440);

        Text title = new Text(isEdit ? "✏  Edit Movie" : "＋  Add New Movie");
        title.setFont(Font.font("Arial", FontWeight.BOLD, 20));
        title.setFill(Color.WHITE);

        // Fields
        TextField titleF  = field("Movie Title",     existing != null ? existing.getTitle()             : "");
        TextField genreF  = field("Genre",            existing != null ? existing.getGenre()             : "");
        TextField durF    = field("Duration (e.g. 2h 10m)", existing != null ? existing.getDuration()   : "");
        TextField ratingF = field("Rating (PG/PG-13/R)", existing != null ? existing.getRating()        : "PG-13");
        TextField priceF  = field("Base Ticket Price ($)", existing != null ? String.valueOf(existing.getBasePrice()) : "");
        TextArea descF    = new TextArea(existing != null ? existing.getDescription() : "");
        descF.setPromptText("Movie description…");
        descF.setPrefRowCount(3);
        descF.setWrapText(true);
        descF.setStyle("-fx-background-color: rgba(255,255,255,0.07);" +
                       "-fx-text-fill: white; -fx-border-color: #30363d;" +
                       "-fx-border-radius: 6; -fx-background-radius: 6;");

        Label errLbl = new Label();
        errLbl.setStyle("-fx-text-fill: #fc8181;");

        Button saveBtn = new Button(isEdit ? "Save Changes" : "Add Movie");
        saveBtn.setMaxWidth(Double.MAX_VALUE);
        saveBtn.setStyle("-fx-background-color: #e94560; -fx-text-fill: white;" +
                         "-fx-font-size: 14; -fx-padding: 10; -fx-background-radius: 8; -fx-cursor: hand;");

        saveBtn.setOnAction(e -> {
            String t   = titleF.getText().trim();
            String g   = genreF.getText().trim();
            String dur = durF.getText().trim();
            String rat = ratingF.getText().trim();
            String desc= descF.getText().trim();
            String pr  = priceF.getText().trim();

            if (t.isEmpty() || g.isEmpty() || dur.isEmpty() || pr.isEmpty()) {
                errLbl.setText("⚠ Please fill in all required fields."); return;
            }
            double price;
            try { price = Double.parseDouble(pr); }
            catch (NumberFormatException ex) { errLbl.setText("⚠ Invalid price."); return; }

            Movie m = new Movie(t, g, dur, price, desc, rat);
            if (isEdit && existing != null) {
                MovieManager.updateMovie(existing.getTitle(), m);
            } else {
                MovieManager.addMovie(m);
            }
            onSave.run();
            dlg.close();
        });

        root.getChildren().addAll(
            title,
            lbl("Title"),   titleF,
            lbl("Genre"),   genreF,
            lbl("Duration"), durF,
            lbl("Rating"),   ratingF,
            lbl("Base Price"), priceF,
            lbl("Description"), descF,
            errLbl, saveBtn
        );

        dlg.setScene(new Scene(root));
        dlg.show();
    }

    private static TextField field(String prompt, String value) {
        TextField tf = new TextField(value);
        tf.setPromptText(prompt);
        tf.setStyle("-fx-background-color: rgba(255,255,255,0.07); -fx-text-fill: white;" +
                    "-fx-border-color: #30363d; -fx-border-radius: 6;" +
                    "-fx-background-radius: 6; -fx-padding: 8; -fx-font-size: 13;");
        return tf;
    }

    private static Label lbl(String text) {
        Label l = new Label(text);
        l.setStyle("-fx-text-fill: #8b949e; -fx-font-size: 12;");
        return l;
    }
}
